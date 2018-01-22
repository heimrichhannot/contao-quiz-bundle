<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\QuizBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class QuizForm extends AbstractType
{
    const ANSWERS_NAME  = 'answers';
    const ANSWER_NAME   = 'answer';
    const QUESTION_NAME = 'question';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $builder->setMethod('GET');

        $builder->add(static::ANSWER_NAME, ChoiceType::class, [
            'label'       => $data[static::QUESTION_NAME]['text'],
            'choices'     => $this->getAnswers($data[static::ANSWERS_NAME]),
            'choice_attr' => function ($val, $key, $index) {
                return ['onClick' => 'this.form.submit()', 'class' => 'answer'];
            },
            'expanded'    => true,
            'multiple'    => false,
            'attr'        => ['class' => 'question'],
        ]);

        $builder->add(static::QUESTION_NAME, HiddenType::class, [
            'data' => $data[static::QUESTION_NAME]['id'],
        ]);
    }

    public function getBlockPrefix()
    {
        return 'huhq';
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function getAnswers($data)
    {
        $answers = [];

        foreach ($data as $key => $value) {
            $answers[$value] = $key;
        }

        return $answers;
    }
}