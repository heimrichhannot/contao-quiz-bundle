<?php


/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], 4, [
    'quiz' => [
        'tables' => ['tl_quiz', 'tl_quiz_question', 'tl_quiz_answer', 'tl_quiz_answer_solving'],
    ],
]);


/**
 * Frontend modules
 */
array_insert($GLOBALS['FE_MOD']['quiz'], 3, [
    'quiz' => 'HeimrichHannot\QuizBundle\Module\ModuleQuiz',
]);


/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'quizs';
$GLOBALS['TL_PERMISSIONS'][] = 'quizp';

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_quiz']                = 'HeimrichHannot\QuizBundle\Model\QuizModel';
$GLOBALS['TL_MODELS']['tl_quiz_question']       = 'HeimrichHannot\QuizBundle\Model\QuizQuestionModel';
$GLOBALS['TL_MODELS']['tl_quiz_answer']         = 'HeimrichHannot\QuizBundle\Model\QuizAnswerModel';
$GLOBALS['TL_MODELS']['tl_quiz_answer_solving'] = 'HeimrichHannot\QuizBundle\Model\QuizAnswerSolvingModel';