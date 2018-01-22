<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Backend;


use Contao\Backend;
use Contao\Input;
use Contao\System;

class TlContent extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Check permissions to edit table tl_content
     */
    public function checkPermission(string $table, string $ptable)
    {
        if ($this->User->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!\is_array($this->User->quiz) || empty($this->User->quiz)) {
            $root = [0];
        } else {
            $root = $this->User->quiz;
        }

        // Check the current action
        switch (Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case '': // empty
            case 'create':
            case 'select':
                // Check access to the news item
                $this->checkAccessToElement(CURRENT_ID, $root, $table, $ptable, true);
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                // Check access to the parent element if a content element is moved
                if (Input::get('act') == 'cutAll' || Input::get('act') == 'copyAll') {
                    $this->checkAccessToElement(Input::get('pid'), $root, $table, $ptable, (Input::get('mode') == 2));
                }

                $objCes = $this->Database->prepare("SELECT id FROM tl_content WHERE ptable=" . $ptable . " AND pid=?")->execute(CURRENT_ID);

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
                $objSession = System::getContainer()->get('session');

                $session                   = $objSession->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objCes->fetchEach('id'));
                $objSession->replace($session);
                break;

            case 'cut':
            case 'copy':
                // Check access to the parent element if a content element is moved
                $this->checkAccessToElement(Input::get('pid'), $root, $table, $ptable, (Input::get('mode') == 2));
            // NO BREAK STATEMENT HERE

            default:
                // Check access to the content element
                $this->checkAccessToElement(Input::get('id'), $root, $table, $ptable);
                break;
        }
    }


    /**
     * Check access to a particular content element
     *
     * @param integer $id
     * @param array   $root
     * @param boolean $blnIsPid
     *
     * @throws \Contao\CoreBundle\Exception\AccessDeniedException
     */
    protected function checkAccessToElement($id, $root, string $table, string $ptable, $blnIsPid = false)
    {
        if ($blnIsPid) {
            $objArchive = $this->Database->prepare("SELECT a.id, n.id AS nid FROM " . $table . " n, " . $ptable . " a WHERE n.id=? AND n.pid=a.id")->limit(1)->execute($id);
        } else {
            $objArchive = $this->Database->prepare("SELECT a.id, n.id AS nid FROM tl_content c, " . $table . " n, " . $ptable . " a WHERE c.id=? AND c.pid=n.id AND n.pid=a.id")->limit(1)->execute($id);
        }

        // Invalid ID
        if ($objArchive->numRows < 1) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid quiz content element ID ' . $id . '.');
        }

        // The news archive is not mounted
        if (!\in_array($objArchive->id, $root)) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to modify article ID ' . $objArchive->nid . ' in quiz ID ' . $objArchive->id . '.');
        }
    }
}