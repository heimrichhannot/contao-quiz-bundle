<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Manager;

use Contao\System;
use Firebase\JWT\JWT;
use HeimrichHannot\Haste\Util\Url;

class TokenManager
{
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
            \Controller::redirect(Url::addQueryString('token='.$encode, System::getContainer()->get('request_stack')->getCurrentRequest()->getUri()));
        }

        if (!isset($decoded->session) || $decoded->session !== System::getContainer()->get('session')->getId()) {
            $token = ['session' => System::getContainer()->get('session')->getId()];
            $encode = JWT::encode($token, System::getContainer()->getParameter('secret'), ['HS256']);
            \Controller::redirect(Url::addQueryString('token='.$encode, System::getContainer()->get('request_stack')->getCurrentRequest()->getUri()));
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
        }

        return $decoded;
    }
}
