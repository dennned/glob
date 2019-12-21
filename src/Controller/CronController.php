<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/cron")
 * Class CronController
 * @package App\Controller
 */
class CronController extends AbstractController
{
    /**
     * @Route("/start/{user}/{password}", methods={"GET"}, name="start_cron")
     *
     * @param Request $request
     * @return Response
     */
    public function startCron(Request $request) :Response
    {
        // check user
        $isUser = $this->checkUser($request->attributes->get('user'), $request->attributes->get('password'));
        if(false === $isUser) {
            return $this->render('@Twig/Exception/error404.html.twig');
        }

        $process = new Process(self::COMMANDE_START);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->sendMail('cron youtube start done');

        return $this->render('@Twig/Exception/error404.html.twig');
    }

    /**
     * @Route("/tag/{user}/{password}", methods={"GET"}, name="tag_cron")
     *
     * @param Request $request
     * @return Response
     */
    public function startTagCron(Request $request) :Response
    {
        // check user
        $isUser = $this->checkUser($request->attributes->get('user'), $request->attributes->get('password'));
        if(false === $isUser) {
            return $this->render('@Twig/Exception/error404.html.twig');
        }

        $process = new Process(self::COMMANDE_TAG_START);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->sendMail('cron youtube tag done');

        return $this->render('@Twig/Exception/error404.html.twig');
    }

    /**
     * @Route("/post/{user}/{password}", methods={"GET"}, name="post_cron")
     *
     * @param Request $request
     * @return Response
     */
    public function startPostCron(Request $request) :Response
    {
        // check user
        $isUser = $this->checkUser($request->attributes->get('user'), $request->attributes->get('password'));
        if(false === $isUser) {
            return $this->render('@Twig/Exception/error404.html.twig');
        }

        $process = new Process(self::COMMANDE_POST_START);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->sendMail('cron youtube post done');

        return $this->render('@Twig/Exception/error404.html.twig');
    }

    /**
     * @param $user
     * @param $password
     * @return bool
     */
    protected function checkUser($user, $password): bool
    {
        return $user === self::USER && $password === self::PASSWORD;
    }

    /**
     * @param string $msg
     */
    public function sendMail(string $msg): void
    {
        $to = self::MAIL_TO;
        $subject = "DennedBlog - ".$msg;
        $txt = $msg.' - '.date('d/m/Y H:i');
        $headers = "From: ".self::MAIL_FROM;

        mail($to,$subject,$txt,$headers);
    }

}
