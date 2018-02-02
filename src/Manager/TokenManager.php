<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Firebase\JWT\JWT;
use HeimrichHannot\Haste\Util\Url;

class TokenManager
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
     * @param $token
     * @param $data
     * @param $key
     *
     * @return string
     */
    public function addDataToJwtToken($token, $data, $key)
    {
        try {
            $decoded = JWT::decode($token, System::getContainer()->getParameter('secret'), ['HS256']);
        } catch (\Exception $e) {
            $token = ['session' => System::getContainer()->get('session')->getId()];
            $encode = JWT::encode($token, System::getContainer()->getParameter('secret'));
            $url = System::getContainer()->get('contao.framework')->getAdapter(Url::class)->addQueryString('token='.$encode, System::getContainer()->get('request_stack')->getCurrentRequest()->getUri());
            System::getContainer()->get('contao.framework')->getAdapter(Controller::class)->redirect($url);
        }

        if (!isset($decoded->session) || $decoded->session !== System::getContainer()->get('session')->getId()) {
            $token = ['session' => System::getContainer()->get('session')->getId()];
            $encode = JWT::encode($token, System::getContainer()->getParameter('secret'));
            $url = System::getContainer()->get('contao.framework')->getAdapter(Url::class)->addQueryString('token='.$encode, System::getContainer()->get('request_stack')->getCurrentRequest()->getUri());
            System::getContainer()->get('contao.framework')->getAdapter(Controller::class)->redirect($url);
        }

        $decoded->$key = $data;

        return JWT::encode($decoded, System::getContainer()->getParameter('secret'));
    }

    /**
     * @param string $token
     *
     * @return object The JWT's payload as a PHP object
     */
    public function getDataFromJwtToken($token)
    {
        try {
            $decoded = JWT::decode($token, System::getContainer()->getParameter('secret'), ['HS256']);
        } catch (\Exception $e) {
            $token = ['session' => System::getContainer()->get('session')->getId()];
            $encode = JWT::encode($token, System::getContainer()->getParameter('secret'));
            $url = System::getContainer()->get('contao.framework')->getAdapter(Url::class)->addQueryString('token='.$encode, System::getContainer()->get('request_stack')->getCurrentRequest()->getUri());
            System::getContainer()->get('contao.framework')->getAdapter(Controller::class)->redirect($url);
        }

        return $decoded;
    }
}
