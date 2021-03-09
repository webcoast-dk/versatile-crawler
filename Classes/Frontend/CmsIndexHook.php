<?php

namespace WEBcoast\VersatileCrawler\Frontend;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\IndexedSearch\Indexer;
use WEBcoast\VersatileCrawler\Crawler\FrontendRequestCrawler;
use WEBcoast\VersatileCrawler\Domain\Model\Item;

class CmsIndexHook extends AbstractIndexHook
{
    public function processIndexing(Item $item, TypoScriptFrontendController &$typoScriptFrontendController, FrontendRequestCrawler $crawler)
    {
        /** @var LanguageAspect $languageAspect */
        $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');
        if ($languageAspect->getId() === $languageAspect->getContentId()) {
            $data = $item->getData();
            $indexer = GeneralUtility::makeInstance(Indexer::class);
            $indexer->conf = [];
            $indexer->conf['id'] = $typoScriptFrontendController->id;
            $indexer->conf['type'] = $typoScriptFrontendController->type;
            $indexer->conf['sys_language_uid'] = $languageAspect->getId();
            $indexer->conf['MP'] = $typoScriptFrontendController->MP;
            $indexer->conf['gr_list'] = implode(',', GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'groupIds', [0, -1]));;
            $indexer->conf['cHash'] = $typoScriptFrontendController->cHash;
            $indexer->conf['cHash_array'] = $typoScriptFrontendController->cHash_array;
            $indexer->conf['staticPageArguments'] = [];
            /** @var PageArguments $pageArguments */
            if ($GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
                $pageArguments = $GLOBALS['TYPO3_REQUEST']->getAttribute('routing', null);
                if ($pageArguments instanceof PageArguments) {
                    $indexer->conf['staticPageArguments'] = $pageArguments->getStaticArguments();
                }
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
            $indexer->conf['freeIndexUid'] = (isset($data['rootConfigurationId']) ? $data['rootConfigurationId'] : 0);
            $indexer->conf['freeIndexSetId'] = 0;

            // use this to override `crdate` and `mtime` and other information (used for record indexing)
            $crawler->enrichIndexData($item, $typoScriptFrontendController, $indexer);

            $indexer->init();
            $indexer->indexTypo3PageContent();
        }
    }
}
