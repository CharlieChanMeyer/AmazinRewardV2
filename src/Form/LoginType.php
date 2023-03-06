<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => "アンケートに回答する際に使用したメール: ",
                'attr' => [
                    "placeholder" => "メールを入力してください。"
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => "パスワード: ",
                'attr' => [
                    "placeholder" => "パスワードを入力してください。"
                ]
            ])
            ->add('rewardCode', IntegerType::class, [
                'label' => "特典コード: ",
                'attr' => [
                    "placeholder" => "特典コードを入力してください。"
                ]
            ])
            ->add('submit', SubmitType::class)
        ;
    }
}
