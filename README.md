# contao-quiz-bundle

This bundle offers a simple quiz with submission (if needed).

### Installing
With composer and Contao 4 Managed Edition:
```
composer require heimrichhannot/contao-quiz-bundle ~1.0
```
### Features
* creating simple quiz with custom evaluation, answers and answer descriptions
* submission
* adding content element to evaluation, answer and answer description

## Creating a quiz
![alt quiz](/docs/screenshot-new-quiz.png)

### Adding submission to quiz
![alt submission](/docs/screenshot-add-submission.png)

### Adding evaluation to quiz
![alt evaluation](/docs/screenshot-add-evaluation.png)

### Adding questions to quiz
![alt question](/docs/screenshot-add-question.png)

### Adding answers to question
![alt_answer](/docs/screenshot-add-answer.png)

### Adding answer description to answer
If you want to explain why the answer is whether correct or false, just simply add a description to the answer.
Otherwise there will just stand correct or false as solving.
![alt answerDescription](/docs/screenshot-add-answer-description.png)



### InsertTags

Tag | Arguments | Example | Description 
--- | --------- | ------- | -------
huh_quiz_total_score | QUIZ_ID | {{huh_quiz_total_score::8}} | Returns the total possible score of the quiz
huh_quiz_current_score | - | {{huh_quiz_current_score}} | Returns the current score of the "player" from session
huh_quiz | MODULE_ID,QUIZ_ID | {{huh_quiz::12::8}} | Returns the quiz with the given id