<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use App\Form\ResetPasswordType;
use App\Form\LoginType;

use App\Entity\Users;
use App\Entity\Events;
use App\Entity\CodeAmazon;
use App\Entity\HistoryLog;
use App\Entity\NbCodeUserEvent;

class MainController extends AbstractController
{
    #[Route('/event/{eventID}/', name: 'app_main_index', requirements: ['eventID' => '\d+'])]
    public function index(EntityManagerInterface $entityManager,Request $request,int $eventID): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }
        $session->set('eventID', $eventID);

        $userRepo = $entityManager->getRepository(Users::class);
        $loginForm = $this->createForm(LoginType::class);
        
        $loginForm->handleRequest($request);
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $email = $loginForm->get('email')->getData();
            $password = $loginForm->get('password')->getData();
            $user = $userRepo->findOneBy([
                'email' => $email,
            ]);
            $userID = $user->getId();
            $success = null;
            if ($user != null) {
                $hpass = $user->getpassword();
                if (!password_verify($password, $hpass)) {
                    $success = "パスワードが正しくありません。";
                } else {
                    $eventRepo = $entityManager->getRepository(Events::class);
                    $user = $userRepo->find($userID);
                    $event = $eventRepo->find($eventID);
                    $userEvents = ($user->getEvents())->toArray();
                    if (!in_array($event,$userEvents)){
                        $success = "ご案内のイベントには登録されていないようです";
                    }
                }
            } else {
                $success = "入力されたメールはシステムに登録されていません";
            }
            if ($success == null) {
                $session->set('userID', $userID);
                return $this->redirectToRoute('app_main_reward');
            } else {
                $session->set('message', $success);
                return $this->redirectToRoute('app_main_index', array('eventID' => $eventID));
            }
            
        }
        
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'message' => $message,
            'form' => $loginForm
        ]);
    }

    #[Route('/reward', name: 'app_main_reward')]
    public function reward(EntityManagerInterface $entityManager,Request $request): Response
    {
        $session = $request->getSession();
        $userID = $session->get('userID');
        $session->set('userID',null);
        $eventID = $session->get('eventID');
        $session->set('eventID',null);

        $userRepo = $entityManager->getRepository(Users::class);
        $eventRepo = $entityManager->getRepository(Events::class);
        $nbCodeRepo = $entityManager->getRepository(NbCodeUserEvent::class);
        $user = $userRepo->find($userID);
        $event = $eventRepo->find($eventID);
        $userEvents = ($user->getEvents())->toArray();

        if (in_array($event,$userEvents)) {
            $codeRepo = $entityManager->getRepository(CodeAmazon::class);
            $historyRepo = $entityManager->getRepository(HistoryLog::class);
            $nbCode = $nbCodeRepo->findOneBy([
                'User' => $user,
                'Event' => $event,
            ]);
            $oldcode = $historyRepo->findBy([
                'event_id' => $event,
                'email_id' => $user,
            ]);
            $codeArray = array();
            if (count($oldcode) == 0) {
                if ($nbCode != null) {
                    $code = $codeRepo->findBy([
                        'event' => $event,
                        'used' => 0
                    ],null,$nbCode->getNbCode());
                } else {
                    $code = $codeRepo->findBy([
                        'event' => $event,
                        'used' => 0
                    ],null,$event->getNbCodeGift());
                }
                if ($nbCode->getNbCode() == sizeof($code)) {
                    foreach ($code as $codeO) {
                        $codeO->setUsed(1);
                        $log = new HistoryLog();
                        $log->setEmailId($user);
                        $log->setAmazonCodeId($codeO);
                        $log->setEventId($event);
                        $log->setDatetime(new \DateTime());
                        $historyRepo->save($log);
                        array_push($codeArray,$codeO->getAmazonCode());
                    }
                    $entityManager->flush();
                } else {
                    mb_language("japanese");
                mb_internal_encoding("UTF-8");

                $mail = new PHPMailer(true);

                $mail->CharSet = "iso-2022-jp";
                $mail->Encoding = "7bit";
                $mail->setLanguage('ja', 'PHPMailer/language/');

                $mail->isSMTP();
                $mail->SMTPDebug = false;
                $mail->Debugoutput = 'html';
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;
                $mail->SMTPSecure = 'tls';
                $mail->SMTPAuth = true;
                
                $mail->Username = $event->getSMTPEmail();
                $mail->Password =self::decrypt($event->getSMTPPassword());
                $mail->setFrom($event->getSMTPEmail(), mb_encode_mimeheader('アマゾンコード特典システム'));

                $mail->addAddress("masa229@gmail.com"); 
                $mail->Subject = mb_encode_mimeheader('コード欠損');
                                                    
                $email_body = mb_convert_encoding("Amazon Code Reward。\n
                イベント $eventID でのコードが足りない 。コードの追加をお願いします ","JIS","UTF-8");

                $email_body = wordwrap($email_body,70);

                $mail->msgHTML(mb_convert_encoding("
                <p>Amazon Code Reward。<br />
                イベント $eventID でのコードが足りない 。コードの追加をお願いします</p>","JIS","UTF-8"));

                $mail->AltBody = $email_body;

                if (!$mail->send()) {
                    $message = "Mailer Error $line:\n";
                    $message .= $mail->ErrorInfo;
                } else {
                    $entityManager->flush();
                }

                    $session->set('message', "システムのコードが足りない。管理者に連絡した。明日、再試行してください");
                    return $this->redirectToRoute('app_main_index', array('eventID' => $eventID));
                }
            } else {
                foreach ($oldcode as $code) {
                    array_push($codeArray,($codeRepo->find($code->getAmazonCodeId()))->getAmazonCode());
                }
            }
            
        }


        return $this->render('main/reward.html.twig', [
            'controller_name' => 'MainController',
            'email' => $user->getEmail(),
            'event_id' => $eventID,
            'codeArray' => $codeArray,
        ]);
    }

    #[Route('/resetpassword', name: 'app_main_resetpassword')]
    public function resetpassword(EntityManagerInterface $entityManager,Request $request): Response
    {
        $userRepo = $entityManager->getRepository(Users::class);
        $session = $request->getSession();
        $eventID = $session->get('eventID');
        $resetForm = $this->createForm(ResetPasswordType::class);

        $resetForm->handleRequest($request);
        if ($resetForm->isSubmitted() && $resetForm->isValid()) {
            $email = $resetForm->get('email')->getData();
            $user = $userRepo->findOneBy([
                'email' => $email,
            ]);
            $eventRepo = $entityManager->getRepository(Events::class);
            $event = $eventRepo->find(1);
            if ($user != null) {
                $pass = self::generateStrongPassword();
                $hpass = password_hash($pass, PASSWORD_DEFAULT);
                $user->setPassword($hpass);

                mb_language("japanese");
                mb_internal_encoding("UTF-8");

                $mail = new PHPMailer(true);

                $mail->CharSet = "iso-2022-jp";
                $mail->Encoding = "7bit";
                $mail->setLanguage('ja', 'PHPMailer/language/');

                $mail->isSMTP();
                $mail->SMTPDebug = false;
                $mail->Debugoutput = 'html';
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;
                $mail->SMTPSecure = 'tls';
                $mail->SMTPAuth = true;
                
                $mail->Username = $event->getSMTPEmail();
                $mail->Password =self::decrypt($event->getSMTPPassword());
                $mail->setFrom($event->getSMTPEmail(), mb_encode_mimeheader('大阪公立大学　大学院情報学研究科　基幹情報学専攻　岩村雅一'));

                $mail->addAddress($email); 
                $mail->Subject = mb_encode_mimeheader('アンケート回答の謝礼');
                                                    
                $email_body = mb_convert_encoding("「食」の好みに関するアンケートにご回答いただいた皆様へ\n\n大阪公立大学の岩村です。\n
                この度は私共のアンケートにご回答いただき、どうもありがとうございました。\n
                諸事情により時間がかかってしまいましたが、Amazonギフトカード(Eメールタイプ)の準備が出来ました。\n
                こちらのリンクをクリックして、受領をお願いいたします。\n
                http://ec2-15-168-13-179.ap-northeast-3.compute.amazonaws.com/thesis-survey/\n\n
                ログインに必要なメールアドレスは、アンケートの回答時に入力していただいたものです(このメールは、そのメールアドレスにお送りしています)。\n
                パスワードはこちらになります: ","JIS","UTF-8").$pass.
                mb_convert_encoding("\n\n大阪公立大学 大学院情報学研究科 基幹情報学専攻 岩村雅一","JIS","UTF-8");

                $email_body = wordwrap($email_body,70);

                $mail->msgHTML(mb_convert_encoding("
                <p>「食」の好みに関するアンケートにご回答いただいた皆様へ</p>
                <p>大阪公立大学の岩村です。<br />
                この度は私共のアンケートにご回答いただき、どうもありがとうございました。<br />
                諸事情により時間がかかってしまいましたが、Amazonギフトカード(Eメールタイプ)の準備が出来ました。<br />
                <a href='http://ec2-15-168-13-179.ap-northeast-3.compute.amazonaws.com/thesis-survey/'>こちらのリンク</a>をクリックして、受領をお願いいたします。<br />
                <p>ログインに必要なメールアドレスは、アンケートの回答時に入力していただいたものです(このメールは、そのメールアドレスにお送りしています)。<br />
                パスワードはこちらになります: ","JIS","UTF-8").$pass . mb_convert_encoding("</p>\n
                <p>大阪公立大学 大学院情報学研究科 基幹情報学専攻 岩村雅一</p>","JIS","UTF-8"));

                $mail->AltBody = $email_body;

                if (!$mail->send()) {
                    $message = "Mailer Error $line:\n";
                    $message .= $mail->ErrorInfo;
                } else {
                    $entityManager->flush();
                }
            }
            $message = "メールが正しければ、新しいパスワードが送信されました。";
            $session->set('message', $message);
            return $this->redirectToRoute('app_main_index', array('eventID' => $eventID));
        }


        return $this->render('main/resetpassword.html.twig', [
            'controller_name' => 'MainController',
            'form' => $resetForm,
            'event_id' => $eventID,
        ]);
    }

    function generateStrongPassword($length = 15, $add_dashes = false, $available_sets = 'luds') {
        $sets = array();
        if(strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if(strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if(strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if(strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?';
    
        $all = '';
        $password = '';
        foreach($sets as $set)
        {
            $password .= $set[self::tweak_array_rand(str_split($set))];
            $all .= $set;
        }
    
        $all = str_split($all);
        for($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[self::tweak_array_rand($all)];
    
        $password = str_shuffle($password);
    
        if(!$add_dashes)
            return $password;
    
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while(strlen($password) > $dash_len)
        {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }

    function tweak_array_rand($array){
        if (function_exists('random_int')) {
            return random_int(0, count($array) - 1);
        } elseif(function_exists('mt_rand')) {
            return mt_rand(0, count($array) - 1);
        } else {
            return array_rand($array);
        }
    }

    private function decrypt(String $toDecrypt): String
    {
        // Store the cipher method
        $ciphering = "AES-128-CTR";

        // Non-NULL Initialization Vector for decryption
        $decryption_iv = '1234567891011121';
        $options = 0;
        
        // Store the decryption key
        $decryption_key = "AzRdIMPLabo324";
        
        // Use openssl_decrypt() function to decrypt the data
        return(openssl_decrypt ($toDecrypt, $ciphering, 
                $decryption_key, $options, $decryption_iv));
    }
}
