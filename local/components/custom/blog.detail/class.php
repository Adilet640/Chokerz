<?php


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Application;

class BlogDetailComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams): array
    {
        $arParams['IBLOCK_TYPE']      = $arParams['IBLOCK_TYPE']      ?? 'blog';
        $arParams['IBLOCK_ID']        = (int)($arParams['IBLOCK_ID']  ?? 0);
        $arParams['ELEMENT_CODE']     = $arParams['ELEMENT_CODE']     ?? '';
        $arParams['CACHE_TIME']       = (int)($arParams['CACHE_TIME'] ?? 3600);
        $arParams['RELATED_COUNT']    = (int)($arParams['RELATED_COUNT'] ?? 4);
        $arParams['LIST_URL']         = $arParams['LIST_URL']         ?? '/blog/';

        return $arParams;
    }

    public function executeComponent(): void
    {
        if (!Loader::includeModule('iblock')) {
            ShowError('Модуль iblock не подключён');
            return;
        }

       
        $code = trim($this->arParams['ELEMENT_CODE'] ?: ($_REQUEST['CODE'] ?? ''));
        if ($code === '') {
            $this->return404();
            return;
        }

        $cacheId  = 'blog_detail_' . $this->arParams['IBLOCK_ID'] . '_' . $code;
        $cacheDir = '/blog/detail/' . $this->arParams['IBLOCK_ID'];
        $cache    = Cache::createInstance();

        if ($cache->initCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)) {
            $this->arResult = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            $tagCache = Application::getInstance()->getTaggedCache();
            $tagCache->startTagCache($cacheDir);
            $tagCache->registerTag('iblock_id_' . $this->arParams['IBLOCK_ID']);

            $item = $this->fetchItem($code);
            if (!$item) {
                $cache->abortDataCache();
                $this->return404();
                return;
            }

            $related = $this->fetchRelated($item);
            $this->arResult = ['ITEM' => $item, 'RELATED' => $related];

            $tagCache->endTagCache();
            $cache->endDataCache($this->arResult);
        }

        if (empty($this->arResult['ITEM'])) {
            $this->return404();
            return;
        }

        // Устанавливаем SEO-данные страницы
        $item = $this->arResult['ITEM'];
        global $APPLICATION;
        $APPLICATION->SetTitle(htmlspecialcharsEx($item['NAME']));
        if (!empty($item['META_DESCRIPTION'])) {
            $APPLICATION->SetPageProperty('description', $item['META_DESCRIPTION']);
        }
        if (!empty($item['META_KEYWORDS'])) {
            $APPLICATION->SetPageProperty('keywords', $item['META_KEYWORDS']);
        }

        $this->IncludeComponentTemplate();
    }

   
    private function fetchItem(string $code): ?array
    {
        $res = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'CODE'      => $code,
                'ACTIVE'    => 'Y',
            ],
            false,
            ['nTopCount' => 1],
            [
                'ID', 'NAME', 'CODE', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE',
                'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'ACTIVE_FROM',
                'TAGS', 'SECTION_ID',
                'META_TITLE', 'META_DESCRIPTION', 'META_KEYWORDS',
            ]
        );

        if (!($el = $res->GetNextElement())) {
            return null;
        }

        $fields = $el->GetFields();
        $props  = $el->GetProperties(['CODE' => ['TYPE', 'VIDEO_ID', 'VIDEO_DURATION', 'VIDEO_EMBED']]);

       
        $detailSrc  = '';
        $previewSrc = '';

        if ($fields['DETAIL_PICTURE']) {
            $file       = \CFile::GetFileArray($fields['DETAIL_PICTURE']);
            $detailSrc  = $file ? \CFile::GetFileSRC($file) : '';
        }
        if ($fields['PREVIEW_PICTURE']) {
            $file       = \CFile::GetFileArray($fields['PREVIEW_PICTURE']);
            $previewSrc = $file ? \CFile::GetFileSRC($file) : '';
        }

        $heroSrc = $detailSrc ?: $previewSrc;

       
        $detailText = $fields['DETAIL_TEXT'] ?? '';
        if ($fields['DETAIL_TEXT_TYPE'] === 'text') {
            $detailText = nl2br(htmlspecialcharsEx($detailText));
        }

      
        $toc = $this->extractToc($detailText);

        return [
            'ID'               => $fields['ID'],
            'NAME'             => $fields['NAME'],
            'CODE'             => $fields['CODE'],
            'DETAIL_TEXT'      => $detailText,
            'HERO_SRC'         => $heroSrc,
            'PREVIEW_SRC'      => $previewSrc,
            'DATE'             => $fields['ACTIVE_FROM'] ? date('d F Y', MakeTimeStamp($fields['ACTIVE_FROM'])) : '',
            'TAGS'             => array_filter(array_map('trim', explode(',', $fields['TAGS'] ?? ''))),
            'SECTION_ID'       => $fields['SECTION_ID'],
            'IS_VIDEO'         => ($props['TYPE']['VALUE'] ?? '') === 'video',
            'VIDEO_EMBED'      => $props['VIDEO_EMBED']['VALUE'] ?? '',
            'VIDEO_ID'         => $props['VIDEO_ID']['VALUE'] ?? '',
            'META_DESCRIPTION' => $fields['META_DESCRIPTION'],
            'META_KEYWORDS'    => $fields['META_KEYWORDS'],
            'TOC'              => $toc,
        ];
    }

  
    private function extractToc(string &$html): array
    {
        $toc = [];
        $i   = 0;

        $html = preg_replace_callback(
            '/<h1([^>]*)>(.*?)<\/h1>/is',
            static function ($matches) use (&$toc, &$i): string {
                $i++;
                $text   = strip_tags($matches[2]);
                $anchor = 'section-' . $i;
                $toc[]  = ['ANCHOR' => $anchor, 'TEXT' => $text];
                return '<h1' . $matches[1] . ' id="' . $anchor . '">' . $matches[2] . '</h1>';
            },
            $html
        );

        return $toc;
    }

    /**
     * «Смотрите также» — похожие материалы (тот же тип или секция)
     */
    private function fetchRelated(array $item): array
    {
        $filter = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE'    => 'Y',
            '!ID'       => $item['ID'],
        ];

        if ($item['SECTION_ID']) {
            $filter['SECTION_ID'] = $item['SECTION_ID'];
        }

        $res = \CIBlockElement::GetList(
            ['RAND' => 'ASC'],
            $filter,
            false,
            ['nTopCount' => $this->arParams['RELATED_COUNT']],
            ['ID', 'NAME', 'CODE', 'DETAIL_PAGE_URL', 'PREVIEW_PICTURE', 'ACTIVE_FROM']
        );

        $result = [];
        while ($el = $res->GetNextElement()) {
            $fields = $el->GetFields();
            $src    = '';
            if ($fields['PREVIEW_PICTURE']) {
                $file = \CFile::GetFileArray($fields['PREVIEW_PICTURE']);
                $src  = $file ? \CFile::GetFileSRC($file) : '';
            }
            $result[] = [
                'ID'         => $fields['ID'],
                'NAME'       => $fields['NAME'],
                'DETAIL_URL' => $fields['DETAIL_PAGE_URL'],
                'PREVIEW_SRC'=> $src,
                'DATE'       => $fields['ACTIVE_FROM'] ? date('d.m.Y', MakeTimeStamp($fields['ACTIVE_FROM'])) : '',
            ];
        }

        return $result;
    }

    /**
     * 404 + остановка компонента
     */
    private function return404(): void
    {
        global $APPLICATION;
        $APPLICATION->Set404();
        ShowError('Материал не найден');
    }
}
