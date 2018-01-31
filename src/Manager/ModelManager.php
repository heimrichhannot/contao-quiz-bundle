<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FilesModel;
use Contao\Model;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;

class ModelManager
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * gets the content element by the given model and table.
     *
     * @param Model  $objModel
     * @param string $table
     *
     * @return Model $objModel
     */
    public function getContentElementByModel(Model $objModel, string $table)
    {
        $id = $objModel->id;
        /** @var ContentModel $adapter */
        $adapter = $this->framework->getAdapter(ContentModel::class);
        $strText = '';
        $objElements = $adapter->findPublishedByPidAndTable($id, $table);
        if (null !== $objElements) {
            foreach ($objElements as $objElement) {
                $strText .= $this->framework->getAdapter(Module::class)->getContentElement($objElement);
            }
        }
        $objModel->hasContentElement = $adapter->countPublishedByPidAndTable($objModel->id, $table) > 0;
        $objModel->contentElement = $strText;

        return $objModel;
    }

    /**
     * add image to given model.
     *
     * @param       $objArticle
     * @param array $templateData
     * @param       $imgSize
     */
    public function addImage($objArticle, array &$templateData, $imgSize)
    {
        // Add an image
        if ($objArticle->addImage && '' !== $objArticle->singleSRC) {
            $imageModel = $this->framework->getAdapter(FilesModel::class)->findByUuid($objArticle->singleSRC);

            if (null !== $imageModel && is_file(TL_ROOT.'/'.$imageModel->path)) {
                // Do not override the field now that we have a model registry (see #6303)
                $imageArray = $objArticle->row();

                // Override the default image size
                if ('' !== $imgSize) {
                    $size = $this->framework->getAdapter(StringUtil::class)->deserialize($imgSize);

                    if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                        $imageArray['size'] = $imgSize;
                    }
                }
                $imageArray['singleSRC'] = $imageModel->path;
                $templateData['images']['singleSRC'] = [];
                System::getContainer()->get('huh.utils.image')->addToTemplateData('singleSRC', 'addImage', $templateData['images']['singleSRC'], $imageArray, null, null, null, $imageModel);
            }
        }
    }

    /**
     * @param Model  $item
     * @param string $text
     * @param string $table
     * @param string $css
     *
     * @return string
     */
    public function parseModel(Model $item, string $text, string $table, string $cssClass, $imgSize)
    {
        /**
         * @var \Twig_Environment
         */
        $twig = System::getContainer()->get('twig');

        $templateData['text'] = $text;
        $item = $this->getContentElementByModel($item, $table);
        $templateData['item'] = $item;
        $templateData['hasContentElement'] = $item->hasContentElement;
        $templateData['contentElement'] = $item->contentElement;
        $templateData['class'] = $cssClass;
        $this->addImage($item, $templateData, $imgSize);

        return $twig->render('@HeimrichHannotContaoQuiz/quiz/quiz_item.html.twig', $templateData);
    }
}
