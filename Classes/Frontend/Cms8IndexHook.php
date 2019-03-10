<?php

namespace WEBcoast\VersatileCrawler\Frontend;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\IndexedSearch\Indexer;
use WEBcoast\VersatileCrawler\Crawler\FrontendRequestCrawler;
use WEBcoast\VersatileCrawler\Domain\Model\Item;

class Cms8IndexHook extends AbstractIndexHook
{
    public function processIndexing(Item $item, TypoScriptFrontendController &$typoScriptFrontendController, FrontendRequestCrawler $crawler)
    {
        $data = $item->getData();
        $indexer = GeneralUtility::makeInstance(Indexer::class);
        $indexer->conf = [];
        $indexer->conf['id'] = $typoScriptFrontendController->id;
        $indexer->conf['type'] = $typoScriptFrontendController->type;
        $indexer->conf['sys_language_uid'] = $typoScriptFrontendController->sys_language_uid;
        $indexer->conf['MP'] = $typoScriptFrontendController->MP;
        $indexer->conf['gr_list'] = $typoScriptFrontendController->gr_list;
        $indexer->conf['cHash'] = $typoScriptFrontendController->cHash;
        $indexer->conf['cHash_array'] = $typoScriptFrontendController->cHash_array;
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
        $indexer->conf['mtime'] = isset($typoScriptFrontendController->register['SYS_LASTCHANGED']) ? $typoScriptFrontendController->register['SYS_LASTCHANGED'] : $typoScriptFrontendController->page['SYS_LASTCHANGED'];
        $indexer->conf['index_externals'] = $typoScriptFrontendController->config['config']['index_externals'];
        $indexer->conf['index_descrLgd'] = $typoScriptFrontendController->config['config']['index_descrLgd'];
        $indexer->conf['index_metatags'] = isset($typoScriptFrontendController->config['config']['index_metatags']) ? $typoScriptFrontendController->config['config']['index_metatags'] : true;
        $indexer->conf['recordUid'] = $crawler->getRecordUid($item);
        $indexer->conf['freeIndexUid'] = (isset($data['rootConfigurationId']) ? $data['rootConfigurationId'] : 0);
        $indexer->conf['freeIndexSetId'] = 0;

        // use this to override `crdate` and `mtime` and other information (used for record indexing)
        $crawler->enrichIndexData($item, $typoScriptFrontendController, $indexer);

        $indexer->init();
        $indexer->indexTypo3PageContent();
    }
}
