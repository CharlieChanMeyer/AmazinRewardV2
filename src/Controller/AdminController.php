<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use App\Form\AdminLoginType;
use App\Form\EventAddDataType;
use App\Form\EventType;
use App\Form\EmailTestType;
use App\Form\ResetPasswordType;

use App\Entity\Users;
use App\Entity\Events;
use App\Entity\CodeAmazon;
use App\Entity\NbCodeUserEvent;
use App\Entity\HistoryLog;

class AdminController extends AbstractController
{
    #[Route('/admin/', name: 'app_admin_index')]
    public function index(EntityManagerInterface $entityManager,Request $request): Response
    {

        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }

        $userRepo = $entityManager->getRepository(Users::class);
        $loginForm = $this->createForm(AdminLoginType::class);

        $loginForm->handleRequest($request);
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $email = $loginForm->get('email')->getData();
            $password = $loginForm->get('password')->getData();
            $user = $userRepo->findOneBy([
                'email' => $email,
                'role' => 1,
            ]);
            $userID = $user->getId();
            $success = null;
            if ($user != null) {
                $hpass = $user->getpassword();
                if (!password_verify($password, $hpass)) {
                    $success = "パスワードが正しくない。";
                }
            } else {
                $success = "表示されたメールは、アクティブリワードには関係ありません。";
            }
            if ($success == null) {
                $session->set('userID', $userID);
                $session->set('time', new \DateTime());
                return $this->redirectToRoute('app_admin_dashboard');
            } else {
                $session->set('message', $success);
                return $this->redirectToRoute('app_admin_index');
            }
            
        }

        return $this->render('admin/index.html.twig', [
            'message' => $message,
            'form' => $loginForm
        ]);
    }

    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager,Request $request): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }
        if ($session->get('userID') == null || $session->get('userID') < 1) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "You need to login to access this page!");
            return $this->redirectToRoute('app_admin_index');
        }
        $time = new \DateTime();
        $timeDiff = $time->diff($session->get('time'));
        if ($timeDiff->h > 2) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "Your session has expired, please login again.");
            return $this->redirectToRoute('app_admin_index');
        }

        return $this->render('admin/dashboard.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/admin/events', name: 'app_admin_events')]
    public function events(EntityManagerInterface $entityManager,Request $request): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }
        if ($session->get('userID') == null || $session->get('userID') < 1) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "You need to login to access this page!");
            return $this->redirectToRoute('app_admin_index');
        }
        $time = new \DateTime();
        $timeDiff = $time->diff($session->get('time'));
        if ($timeDiff->h > 2) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "Your session has expired, please login again.");
            return $this->redirectToRoute('app_admin_index');
        }

        $eventsRepo = $entityManager->getRepository(Events::class);
        $events = $eventsRepo->findAll();

        return $this->render('admin/events.html.twig', [
            'message' => $message,
            'events' => $events,
        ]); 
    }

    #[Route('/admin/event/{id}', name: 'app_admin_event', requirements: ['id' => '\d+'])]
    public function event(EntityManagerInterface $entityManager,Request $request,int $id, SluggerInterface $slugger): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }
        if ($session->get('userID') == null || $session->get('userID') < 1) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "You need to login to access this page!");
            return $this->redirectToRoute('app_admin_index');
        }
        $time = new \DateTime();
        $timeDiff = $time->diff($session->get('time'));
        if ($timeDiff->h > 2) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "Your session has expired, please login again.");
            return $this->redirectToRoute('app_admin_index');
        }

        $eventsRepo = $entityManager->getRepository(Events::class);
        $codeAmazonRepo = $entityManager->getRepository(CodeAmazon::class);
        $nbCodeRepo = $entityManager->getRepository(NbCodeUserEvent::class);
        $historyLogRepo = $entityManager->getRepository(HistoryLog::class);
        $event = $eventsRepo->find($id);
        $codeAmazonUsed = count($codeAmazonRepo->findBy([
            'event' => $event,
            'used' => 1,
        ]));
        $codeAmazonEvent = count($codeAmazonRepo->findBy([
            'event' => $event,
        ]));

        $emailBodyArray = explode("\\n",$event->getEmailBody());
        $altEmailBodyArray = explode("<br />",str_replace(array("<p>","</p>","\\n"),"",$event->getEmailAltBody()));

        $emailBody = implode("<br>",$emailBodyArray);
        $altEmailBody = implode("<br>",$altEmailBodyArray);

        $eventUsersArray = $event->getUsers()->toArray();

        $eventHistoryLog = $event->getHistory()->toArray();

        $eventHistoryUsers = array();
        $logs = array();
        
        foreach ($eventHistoryLog as $history) {
            array_push($eventHistoryUsers, $history->getEmailId());
            array_push($logs, [$history->getEmailId()->getId(),$history->getEmailId()->getEmail(),$history->getDatetime()->format('Y-m-d H:i:s')]);
        }

        $eventUsers= array();
        $totalGotCode = 0;
        foreach ($eventUsersArray as $user) {
            $nbCodeGot = sizeOf($historyLogRepo->findBy([
                "email_id" => $user,
                "event_id" => $event,
            ]));
            $nbCodeTotal = $nbCodeRepo->findOneBy([
                "User" => $user,
                "Event" => $event,
            ])->getNbCode();
            
            if ($nbCodeGot == $nbCodeTotal) {
                $totalGotCode++;
                array_push($eventUsers,array($user->getId(),$user->getEmail(),"$nbCodeGot/$nbCodeTotal"));
            } else {
                array_unshift($eventUsers,array($user->getId(),$user->getEmail(),"$nbCodeGot/$nbCodeTotal"));
            }
        }

        $form = $this->createForm(EventAddDataType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $csvFile */
            $csvFile = $form->get('csv_file')->getData();
            $data = $form->get('data')->getData();

            if ($data == 0) {
                $session->set('message', "Please select the type of data you want to import.");
                return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()),);
            }

            $originalFilename = pathinfo($csvFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$csvFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $csvFile->move(
                    $this->getParameter('csv_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $session->set('message', "There was a problem during the file upload.");
                return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()),);
            }

            if ($data == 2) {
                $nbUsers = count($eventUsersArray);
                if ($nbUsers < $event->getNumberCodes()) {
                    $usersEmailArray = array();
                    foreach ($eventUsersArray as $user) {
                        array_push($usersEmailArray, $user->getEmail());
                    }
                    $lines = file($this->getParameter('csv_directory').'/'.$newFilename);
                    $usersRepo = $entityManager->getRepository(Users::class);
                    foreach($lines as $line) {
                        $line = str_replace(',', '', $line);
                        $line = rtrim($line);
                        if (!in_array($line,$usersEmailArray)) {
                            $pass = self::generateStrongPassword();
                            $hpass = password_hash($pass, PASSWORD_DEFAULT);

                            $newUser = $usersRepo->findOneBy([
                                'email' => $line,
                            ]);

                            if ($newUser == null) {
                                $newUser = new Users();
                                $newUser->setEmail($line);
                                $newUser->setPassword($hpass);
                                $newUser->setRole(0);
                                $newNbCode = new NbCodeUserEvent();
                                $newNbCode->setUser($newUser);
                                $newNbCode->setEvent($event);
                                $newNbCode->setNbCode(1);

                                $nbCodeRepo->save($newNbCode);
                                $newUser->addEvent($event);
                                $usersRepo->save($newUser);

                                $message = self::sendEmail($event,$line,$pass);
                            } else {
                                $nbCode = $nbCodeRepo->findOneBy([
                                    'User' => $newUser,
                                    'Event' => $event,
                                ]);
                                if ($nbCode == null) {
                                    $newUser->setPassword($hpass);
                                    $newNbCode = new NbCodeUserEvent();
                                    $newNbCode->setUser($newUser);
                                    $newNbCode->setEvent($event);
                                    $newNbCode->setNbCode(1);

                                    $nbCodeRepo->save($newNbCode);
                                    $newUser->addEvent($event);
                                    $usersRepo->save($newUser);

                                    $message = self::sendEmail($event,$line,$pass);
                                } else {
                                    $nbCode->setNbCode($nbCode->getNbCode()+1);

                                    $nbCodeRepo->save($newNbCode);
                                }
                                
                            }
                            
                            $entityManager->flush();

                            if ($message == "Error") {
                                $session->set('message', "Email failed to send for $line. Password: $pass");
                                return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()));
                            }
                            
                        } else {
                            $user = $usersRepo->findOneBy([
                                'email' => $line,
                            ]);
                            $nbCode = $nbCodeRepo->findOneBy([
                                'User' => $user,
                                'Event' => $event,
                            ]);
                            $nbCode->setNbCode($nbCode->getNbCode()+1);

                            $nbCodeRepo->save($nbCode);
                            $entityManager->flush();
                        }
                    }
                    $session->set('message', "Emails send");
                    return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()));
                } else {
                    $session->set('message', "The maximum number of users for this event has been reached.");
                    return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()));
                }
            } else if ($data == 1) {
                $nbCode = Count($event->getCodesAmazon()->toArray());
                $nbCodeMax = $event->getNumberCodes() * $event->getNbCodeGift() ;
                if ($nbCode < $nbCodeMax) {
                    $amazonCodeOArray = $event->getCodesAmazon()->toArray();
                    $amazonCodeArray = array();
                    foreach ($amazonCodeOArray as $amazonCode) {
                        array_push($amazonCodeArray, $amazonCode->getAmazonCode());
                    }

                    $lines = file($this->getParameter('csv_directory').'/'.$newFilename);
                    foreach($lines as $line) {
                        $line = str_replace(',', '', $line);
                        $line = rtrim($line);
                        if (!in_array($line,$amazonCodeArray)) {
                            $code = new CodeAmazon();
                            $code->setAmazonCode($line);
                            $code->setEvent($event);
                            $code->setUsed(0);
                            $codeAmazonRepo->save($code);
                        }
                    }
                    $entityManager->flush();
                    $session->set('message', "Codes uploaded");
                    return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()));
                } else {
                    $session->set('message', "The maximum number of code for this event has been reached");
                    return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()));
                }
            }
            
        }

        $emailTestForm = $this->createForm(EmailTestType::class);
        $emailTestForm->handleRequest($request);
        if ($emailTestForm->isSubmitted() && $emailTestForm->isValid()) {
            $email = $emailTestForm->get('email')->getData();
            $message = self::sendEmail($event, $email, "パスワードテスト");

            if ($message == "Error") {
                $session->set('message', "Email failed to send for $line. Password: $pass");
                return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()));
            } else {
                $session->set('message', "Emails send");
                return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()));
            }
        }

        return $this->render('admin/event.html.twig', [
            'message' => $message,
            'event' => $event,
            'codeAmazonUsed' => $codeAmazonUsed,
            'emailBody' => $emailBodyArray,
            'altEmailBody' => $altEmailBodyArray,
            'eventUsers' => $eventUsers,
            'codeAmazonEvent' => $codeAmazonEvent,
            'form' => $form,
            'emailTestForm' => $emailTestForm,
            'totalGotCode' => $totalGotCode,
            'logs' => $logs,
        ]); 
    }

    #[Route('/admin/event/create/', name: 'app_admin_create_event')]
    public function create_event(EntityManagerInterface $entityManager,Request $request, SluggerInterface $slugger): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }
        if ($session->get('userID') == null || $session->get('userID') < 1) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "You need to login to access this page!");
            return $this->redirectToRoute('app_admin_index');
        }
        $time = new \DateTime();
        $timeDiff = $time->diff($session->get('time'));
        if ($timeDiff->h > 2) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "Your session has expired, please login again.");
            return $this->redirectToRoute('app_admin_index');
        }

        $eventsRepo = $entityManager->getRepository(Events::class);
        $event = new Events();

        $form = $this->createForm(EventType::class,$event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setSMTPPassword(self::encrypt($form->get('SMTPPassword')->getData()));
            $event->setClosed(false);
            $eventsRepo->save($event);
            $entityManager->flush();
            $session->set('message', "The event has been saved.");
            return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()),);
        }

        return $this->render('admin/event_edit.html.twig', [
            'message' => $message,
            'event' => $event,
            'form' => $form,
        ]); 

    }

    #[Route('/admin/event/edit/{id}', name: 'app_admin_edit_event', requirements: ['id' => '\d+'])]
    public function edit_event(EntityManagerInterface $entityManager,Request $request,int $id, SluggerInterface $slugger): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }
        if ($session->get('userID') == null || $session->get('userID') < 1) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "You need to login to access this page!");
            return $this->redirectToRoute('app_admin_index');
        }
        $time = new \DateTime();
        $timeDiff = $time->diff($session->get('time'));
        if ($timeDiff->h > 2) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "Your session has expired, please login again.");
            return $this->redirectToRoute('app_admin_index');
        }

        $eventsRepo = $entityManager->getRepository(Events::class);
        $event = $eventsRepo->find($id);

        $form = $this->createForm(EventType::class,$event);
        $form->get('SMTPPassword')->setData(self::decrypt($event->getSMTPPassword()));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $event->setSMTPPassword(self::encrypt($form->get('SMTPPassword')->getData()));
            $eventsRepo->save($event);
            $entityManager->flush();
            $session->set('message', "The edit has been saved.");
            return $this->redirectToRoute('app_admin_event', array('id' => $event->getId()));
        }

        return $this->render('admin/event_edit.html.twig', [
            'message' => $message,
            'event' => $event,
            'form' => $form,
        ]); 

    }

    #[Route('/admin/management/', name: 'app_admin_management')]
    public function management(EntityManagerInterface $entityManager,Request $request, SluggerInterface $slugger): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }
        if ($session->get('userID') == null || $session->get('userID') < 1) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "You need to login to access this page!");
            return $this->redirectToRoute('app_admin_index');
        }
        $time = new \DateTime();
        $timeDiff = $time->diff($session->get('time'));
        if ($timeDiff->h > 2) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "Your session has expired, please login again.");
            return $this->redirectToRoute('app_admin_index');
        }

        $usersRepo = $entityManager->getRepository(Users::class);
        $users = $usersRepo->findBy([
            'role' => 1,
        ]);

        $addAdminForm = $this->createForm(EmailTestType::class);
        $addAdminForm->handleRequest($request);
        if ($addAdminForm->isSubmitted() && $addAdminForm->isValid()) {
            $email = $addAdminForm->get('email')->getData();

            $newUser = $usersRepo->findOneBy([
                'email' => $email,
            ]);

            if ($newUser == null) {
                $newUser = new Users();
                $newUser->setEmail($email);
                $pass = self::generateStrongPassword();
                $hpass = password_hash($pass, PASSWORD_DEFAULT);
                $newUser->setPassword($hpass);
                $newUser->setRole(1);
            } else {
                $newUser->setRole(1);
                $pass = null;
            }
            
            $usersRepo->save($newUser);
            $entityManager->flush();
            $eventRepo = $entityManager->getRepository(Events::class);
            $event = $eventRepo->find(1);
            $message = self::sendEmailAdmin($email,$pass,$event->getSMTPEmail(),$event->getSMTPPassword());

            if ($message == "Error") {
                $session->set('message', "Email failed to send for $line. Password: $pass");
                return $this->redirectToRoute('app_admin_management');
            } else {
                $session->set('message', "Administrator added.");
                return $this->redirectToRoute('app_admin_management');
            }

        }

        return $this->render('admin/management.html.twig', [
            'message' => $message,
            'users' => $users,
            'addAdminForm' => $addAdminForm,
        ]); 

    }

    #[Route('/admin/management/{id}', name: 'app_admin_remove_admin', requirements: ['id' => '\d+'])]
    public function removeAdmin(EntityManagerInterface $entityManager,Request $request,int $id, SluggerInterface $slugger): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }
        if ($session->get('userID') == null || $session->get('userID') < 1) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "You need to login to access this page!");
            return $this->redirectToRoute('app_admin_index');
        }
        $time = new \DateTime();
        $timeDiff = $time->diff($session->get('time'));
        if ($timeDiff->h > 2) {
            $session->set('time', null);
            $session->set('userID', null);
            $session->set('message', "Your session has expired, please login again.");
            return $this->redirectToRoute('app_admin_index');
        }

        $usersRepo = $entityManager->getRepository(Users::class);
        $user = $usersRepo->find($id);

        $user->setRole(0);
        $usersRepo->save($user);
        $entityManager->flush();
        $email = $user->getEmail();
        $session->set('message', "$email has been removed from the admins.");
        return $this->redirectToRoute('app_admin_management');

    }

    #[Route('/admin/event/resetpassword/{event_id}/{user_id}', name: 'app_admin_resetpassword', requirements: ['user_id' => '\d+','event_id' => '\d+'])]
    public function resetPassword(EntityManagerInterface $entityManager,Request $request,int $user_id,int $event_id, SluggerInterface $slugger): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }

        $usersRepo = $entityManager->getRepository(Users::class);
        $user = $usersRepo->find($user_id);

        $eventsRepo = $entityManager->getRepository(Events::class);
        $event = $eventsRepo->find($event_id);

        $pass = self::generateStrongPassword();
        $hpass = password_hash($pass, PASSWORD_DEFAULT);
        $user->setPassword($hpass);
        $email = $user->getEmail();
        $message = self::sendEmail($event,$email,$pass);
        $entityManager->flush();

        if ($message == "Error") {
            $session->set('message', "Email failed to send for $email. New password: $pass");
            return $this->redirectToRoute('app_admin_event', array('id' => $event_id));
        } else {
            $session->set('message', "New password was send to $email. New password: $pass");
            return $this->redirectToRoute('app_admin_event', array('id' => $event_id));
        }
    }

    #[Route('/admin/resetpassword', name: 'app_admin_rpass')]
    public function resetpasswordpage(EntityManagerInterface $entityManager,Request $request): Response
    {
        $session = $request->getSession();
        $message = null;
        if ($session->get('message') != "") {
            $message = $session->get('message');
            $session->set('message', "");
        }

        $usersRepo = $entityManager->getRepository(Users::class);

        $resetForm = $this->createForm(ResetPasswordType::class);

        $resetForm->handleRequest($request);

        if ($resetForm->isSubmitted() && $resetForm->isValid()) {
            $email = $resetForm->get('email')->getData();
            $user = $usersRepo->findOneBy([
                'email' => $email,
            ]);
            if ($user != null) {
                $pass = self::generateStrongPassword();
                $hpass = password_hash($pass, PASSWORD_DEFAULT);
                $user->setPassword($hpass);
                $entityManager->flush();
            }
            $message = "New password for $email: $pass";
            $session->set('message', $message);
            return $this->redirectToRoute('app_admin_rpass');
        }

        return $this->render('admin/ressetpass.html.twig', [
            'message' => $message,
            'form' => $resetForm,
        ]); 
    }

    #[Route('/admin/logout', name: 'app_admin_logout')]
    public function logout(EntityManagerInterface $entityManager,Request $request): Response
    {
        $session = $request->getSession();
        $session->set('time', null);
        $session->set('userID', null);
        return $this->redirectToRoute('app_admin_index');

    }

    private function generateStrongPassword($length = 15, $add_dashes = false, $available_sets = 'luds') {
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

    private function tweak_array_rand($array){
        if (function_exists('random_int')) {
            return random_int(0, count($array) - 1);
        } elseif(function_exists('mt_rand')) {
            return mt_rand(0, count($array) - 1);
        } else {
            return array_rand($array);
        }
    }

    private function sendEmail(Events $event, String $line, String $pass): String
    {
        mb_language("japanese");
        mb_internal_encoding("UTF-8");

        $mail = new PHPMailer(true);

        $mail->CharSet = "iso-2022-jp";
        $mail->Encoding = "7bit";
        $mail->setLanguage('ja', 'PHPMailer/language/');

        $mail->isSMTP();
        $mail->SMTPDebug = false;
        $mail->Debugoutput = 'html';
        if ($event->getSMTP() == 1) {
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = 'tls';
        } else if ($event->getSMTP() == 2) {
            $mail->Host = 'smtpauth.omu.ac.jp';
            $mail->Port = 587;
            $mail->SMTPSecure = 'STARTTLS';
        }
        $mail->SMTPAuth = true;
        
        $mail->Username = $event->getSMTPEmail();
        $mail->Password = self::decrypt($event->getSMTPPassword());
        if ($event->getSMTP() == 1) {
            $mail->setFrom($event->getSMTPEmail(), mb_encode_mimeheader($event->getEmailHeader()));
        } else if ($event->getSMTP() == 2) {
            $mail->setFrom($event->getSMTPEmail()."@st.omu.ac.jp", mb_encode_mimeheader($event->getEmailHeader()));
        }

        $mail->addAddress($line); 
        $mail->Subject = mb_encode_mimeheader($event->getEmailSubject());
        
        $emailBodyArray = explode("$.$",$event->getEmailBody());
        $altEmailBodyArray = explode("$.$",$event->getEmailAltBody());

        $email_body = "";
        foreach ($emailBodyArray as $ebv) {
            if ($ebv == "code_pass") {
                $email_body .= $pass;
            } else if ($ebv == "code_event") {
                $email_body .= $event->getId();
            } else {
                $email_body .= mb_convert_encoding("$ebv","JIS","UTF-8");
            }  
        }

        $email_body = wordwrap($email_body,70);

        $email_alt_body = "";
        foreach ($altEmailBodyArray as $aebv) {
            if ($aebv == "code_pass") {
                $email_alt_body .= $pass;
            } else if ($aebv == "code_event") {
                $email_alt_body .= $event->getId();
            } else {
                $email_alt_body .= mb_convert_encoding("$aebv","JIS","UTF-8");
            }  
        }

        $mail->msgHTML($email_alt_body);

        $mail->AltBody = $email_body;

        if (!$mail->send()) {
            $message = "Error";
        } else {
            $message = "Emails sent";
        }
        return $message;
    }

    private function sendEmailAdmin(String $line, String $pass = null, String $stmpEmail, String $stmpPassword): String
    {
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
        
        $mail->Username = $stmpEmail;
        $mail->Password = self::decrypt($stmpPassword);
        $mail->setFrom($stmpEmail, mb_encode_mimeheader("Amazon survey reward | Admin"));

        $mail->addAddress($line); 
        $mail->Subject = mb_encode_mimeheader("Administrator access granted for Amazon survey reward website");

        $email_body = mb_convert_encoding("You have gotten administrator access for Amazon survey reward website.\nhttp://ec2-15-168-13-179.ap-northeast-3.compute.amazonaws.com/thesis-survey/\n","JIS","UTF-8");
        if ($pass == null) {
            $email_body .= mb_convert_encoding("Your password wan't reset.","JIS","UTF-8");
        } else {
            $email_body .= mb_convert_encoding("Your password is: ","JIS","UTF-8");
            $email_body .= $pass;
        }
        

        $email_body = wordwrap($email_body,70);

        $email_alt_body = mb_convert_encoding("<p>You have gotten administrator access for Amazon survey reward website.<br /><a href='http://ec2-15-168-13-179.ap-northeast-3.compute.amazonaws.com/thesis-survey/'>こちらのリンク</a><br />","JIS","UTF-8");
        if ($pass == null) {
            $email_alt_body .= mb_convert_encoding("Your password wan't reset.</p>","JIS","UTF-8");
        } else {
            $email_alt_body .= mb_convert_encoding("Your password is: ","JIS","UTF-8");
            $email_alt_body .= $pass.mb_convert_encoding("</p>","JIS","UTF-8");
        }

        $mail->msgHTML($email_alt_body);

        $mail->AltBody = $email_body;

        if (!$mail->send()) {
            $message = "Error";
        } else {
            $message = "Emails sent";
        }
        return $message;
    }

    private function encrypt(String $toCrypt): String
    {
        // Store the cipher method
        $ciphering = "AES-128-CTR";

        // Use OpenSSl Encryption method
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = 0;

        // Non-NULL Initialization Vector for encryption
        $encryption_iv = '1234567891011121';

        // Store the encryption key
        $encryption_key = "AzRdIMPLabo324";

        // Use openssl_encrypt() function to encrypt the data
        return(openssl_encrypt($toCrypt, $ciphering,
                $encryption_key, $options, $encryption_iv));
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
