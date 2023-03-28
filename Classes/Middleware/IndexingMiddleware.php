<?php

declare(strict_types=1);

namespace WEBcoast\VersatileCrawler\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\IndexedSearch\Indexer;
use WEBcoast\VersatileCrawler\Controller\CrawlerController;
use WEBcoast\VersatileCrawler\Controller\QueueController;
use WEBcoast\VersatileCrawler\Crawler\FrontendRequestCrawler;
use WEBcoast\VersatileCrawler\Domain\Model\Item;
use WEBcoast\VersatileCrawler\Queue\Manager;
use WEBcoast\VersatileCrawler\Utility\TypeUtility;

class IndexingMiddleware implements MiddlewareInterface
{
    public const HASH_HEADER = 'X-Versatile-Crawler-Hash';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->hasHeader(self::HASH_HEADER)) {
            $hash = $request->getHeader(self::HASH_HEADER);
            if (is_array($hash)) {
                $hash = array_shift($hash);
            }

            if (empty($hash)) {
                return new JsonResponse(
                    [
                        'state' => 'error',
                        'message' => 'Crawler hash must not be empty',
                    ],
                    400
                );
            }

            $queueManager = GeneralUtility::makeInstance(Manager::class);
            $itemResult = $queueManager->getItemForProcessing($hash);
            $itemRecord = $itemResult->fetch();
            if (!is_array($itemRecord)) {
                return new JsonResponse(
                    [
                        'message' => 'No item for crawling for given hash',
                    ],
                    404
                );
            }

            $item = $queueManager->getFromRecord($itemRecord);
            $configurationResult = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable(QueueController::CONFIGURATION_TABLE)
                ->select(['*'], QueueController::CONFIGURATION_TABLE, ['uid' => $item->getConfiguration()]);
            $configuration = $configurationResult->fetchAssociative();
            if (!is_array($configuration)) {
                return new JsonResponse(
                    [
                        'message' => 'No crawling configuration found the given item',
                    ],
                    412
                );
            }
            $className = TypeUtility::getClassForType($configuration['type']);
            $crawler = GeneralUtility::makeInstance($className);
            if (!$crawler instanceof FrontendRequestCrawler) {
                return new JsonResponse(
                    [
                        'message' => sprintf(
                            'The class "%s" must extend "%s", to be used for frontend indexing.',
                            get_class($crawler),
                            FrontendRequestCrawler::class
                        ),
                    ],
                    400
                );
            }

            $controller = $request->getAttribute('frontend.controller') ?? $GLOBALS['TSFE'];
            if (!$crawler->isIndexingAllowed($item, $controller)) {
                return new JsonResponse(
                    [
                        'message' => 'The indexing was denied. This should not happen.',
                    ],
                    403
                );
            }

            // Continue with normal request processing, to have the content in the frontend controller
            $handler->handle($request);
            $this->processIndexing($item, $controller, $crawler, $request);

            return new JsonResponse([]);
        }

        // Not a crawler request, continue with normal request processing
        return $handler->handle($request);
    }

    public function processIndexing(Item $item, TypoScriptFrontendController &$typoScriptFrontendController, FrontendRequestCrawler $crawler, ServerRequestInterface $request)
    {
        /** @var LanguageAspect $languageAspect */
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        if ($languageAspect->getId() === $languageAspect->getContentId()) {
            $data = $item->getData();
            $rootConfiguration = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(CrawlerController::CONFIGURATION_TABLE)->select(
                ['*'],
                CrawlerController::CONFIGURATION_TABLE,
                ['uid' => $data['rootConfigurationId']],
                [],
                [],
                1
            )->fetchAssociative();
            $indexer = GeneralUtility::makeInstance(Indexer::class);
            $indexer->conf = [];
            $indexer->conf['id'] = $typoScriptFrontendController->id;
            $indexer->conf['type'] = $typoScriptFrontendController->type;
            $indexer->conf['sys_language_uid'] = $languageAspect->getId();
            $indexer->conf['MP'] = $typoScriptFrontendController->MP;
            $indexer->conf['gr_list'] = implode(',', GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'groupIds', [0, -1]));;
            if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version()) < VersionNumberUtility::convertVersionNumberToInteger('10.0.0')) {
                // These two properties do not exist anymore in TYPO3 CMS 10
                $indexer->conf['cHash'] = $typoScriptFrontendController->cHash;
                $indexer->conf['cHash_array'] = $typoScriptFrontendController->cHash_array;
            }
            $indexer->conf['staticPageArguments'] = [];
            $pageArguments = $request->getAttribute('routing');
            if ($pageArguments instanceof PageArguments) {
                $indexer->conf['staticPageArguments'] = $pageArguments->getStaticArguments();
            }
            $indexer->conf['crdate'] = $typoScriptFrontendController->page['crdate'];
            $indexer->conf['page_cache_reg1'] = $typoScriptFrontendController->page_cache_reg1;
            $indexer->conf['rootline_uids'] = [];
            foreach ($typoScriptFrontendController->config['rootLine'] as $rlkey => $rldat) {
                $indexer->conf['rootline_uids'][$rlkey] = $rldat['uid'];
            }
            $indexer->conf['content'] = $typoScriptFrontendController->content;
            $indexer->conf['indexedDocTitle'] = $typoScriptFrontendController->convOutputCharset(
                !empty($typoScriptFrontendController->altPageTitle) ? $typoScriptFrontendController->altPageTitle : $typoScriptFrontendController->indexedDocTitle
            );
            $indexer->conf['metaCharset'] = $typoScriptFrontendController->metaCharset;
            $indexer->conf['mtime'] = $typoScriptFrontendController->register['SYS_LASTCHANGED'] ?? $typoScriptFrontendController->page['SYS_LASTCHANGED'];
            $indexer->conf['index_externals'] = $typoScriptFrontendController->config['config']['index_externals'];
            $indexer->conf['index_descrLgd'] = $typoScriptFrontendController->config['config']['index_descrLgd'];
            $indexer->conf['index_metatags'] = isset($typoScriptFrontendController->config['config']['index_metatags']) ?? true;
            $indexer->conf['recordUid'] = $crawler->getRecordUid($item);
            $indexer->conf['freeIndexUid'] = $rootConfiguration['indexing_configuration'];
            $indexer->conf['freeIndexSetId'] = 0;

            // use this to override `crdate` and `mtime` and other information (used for record indexing)
            $crawler->enrichIndexData($item, $typoScriptFrontendController, $indexer);

            $indexer->init();
            $indexer->indexTypo3PageContent();
        }
    }
}
