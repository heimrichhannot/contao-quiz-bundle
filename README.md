# contao-quiz-bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-quiz-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-quiz-bundle)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-quiz-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-quiz-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-quiz-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-quiz-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-quiz-bundle)

This bundle offers a simple quiz with submission (if needed).

## Warning: This bundle is abandoned

This bundle is abandoned and will not be further developed. We recommend usung [Survey Bundle](https://github.com/pdir/contao-survey).

## Installing
With composer and Contao 4 Managed Edition:
```
composer require heimrichhannot/contao-quiz-bundle ~1.0
```
## Features
* Module QuizReader
* Module QuizSubmission
* creating simple quiz with custom evaluation, answers and answer descriptions
* submission
* adding content element to evaluation, answer and answer description

## Creating a quiz
![alt quiz](/docs/screenshot-new-quiz.png)

### Adding submission to quiz
![alt submission](/docs/screenshot-add-submission.png)

To adding a submission to your quiz, create a quiz submission module, select 'add submission', select your submission archive and add the quiz submission module to your evaluation.
See [here](https://github.com/heimrichhannot/contao-submissions) how to add a submission archive (https://github.com/heimrichhannot/contao-submissions). 

### Adding evaluation to quiz
![alt evaluation](/docs/screenshot-add-evaluation.png)

### Adding questions to quiz
![alt question](/docs/screenshot-add-question.png)

### Adding answers to question
![alt_answer](/docs/screenshot-add-answer.png)

### Adding answer description to answer
If you want to explain why the answer is either correct or false simply add a description to the answer.
Otherwise there will just stand correct or false as solving.
![alt answerDescription](/docs/screenshot-add-answer-description.png)

### InsertTags

Tag | Arguments | Example | Description 
--- | --------- | ------- | -------
huh_quiz_total_score | QUIZ_ID | {{huh_quiz_total_score::8}} | Returns the total possible score of the quiz
huh_quiz_current_score | - | {{huh_quiz_current_score}} | Returns the current score of the "player" from session
huh_quiz | MODULE_ID,QUIZ_ID | {{huh_quiz::12::8}} | Returns the quiz with the given id
