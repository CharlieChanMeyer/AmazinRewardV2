<?php

namespace App\Form;

use App\Entity\Events;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'label' => "Name of the Event: ",
            ])
            ->add('AmazonAmount',IntegerType::class, [
                'label' => "Amount of one amazon code: ",
            ])
            ->add('NumberCodes',IntegerType::class, [
                'label' => "Number of participants that will be receiving code(s): ",
            ])
            ->add('Description',TextareaType::class, [
                'label' => "Description of the event: ",
            ])
            ->add('SMTPEmail',TextType::class, [
                'label' => "Login (Email or id) that will be used to send emails to participants: ",
            ])
            ->add('SMTP',IntegerType::class, [
                'label' => "SMTP to use (1: Gmail, 2: OMU SMTP Server): ",
            ])
            ->add('SMTPPassword',TextType::class, [
                'label' => "SMTP password of the email: ",
            ])
            ->add('EmailHeader',TextType::class, [
                'label' => "Name that will be affiliated to the email: ",
            ])
            ->add('EmailSubject',TextType::class, [
                'label' => "Subject of the email: ",
            ])
            ->add('EmailBody',TextareaType::class, [
                'label' => 'Email body:',
            ])
            ->add('EmailAltBody',TextareaType::class, [
                'label' => 'Email Alt body:',
            ])
            ->add('nbCodeGift',IntegerType::class, [
                'label' => "Number of code per participants: ",
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}
