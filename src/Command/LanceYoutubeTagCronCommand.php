<?php

namespace App\Command;

use App\Entity\CronLog;
use App\Entity\VideoYoutube;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * A console command that creates users and stores them in the database.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:add-user
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:add-user -vv
 *
 * See https://symfony.com/doc/current/console.html
 * For more advanced uses, commands can be defined as services too. See
 * https://symfony.com/doc/current/console/commands_as_services.html
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class LanceYoutubeTagCronCommand extends Command
{
    private const LIMIT_VIDEOS = 5;

    // to make your command lazily loaded, configure the $defaultName static property,
    // so it will be instantiated only when the command is actually called.
    protected static $defaultName = 'cron:youtube-tag-start';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $entityManager;
    private $keyApi;

    public function __construct(EntityManagerInterface $em, string $keyApi)
    {
        parent::__construct();
        $this->entityManager = $em;
        $this->keyApi = $keyApi;
    }


    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Lance cron Youtube and stores them in the database')
        ;
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after initialize() and before execute(). Its purpose
     * is to check if some of the options/arguments are missing and interactively
     * ask the user for those values.
     *
     * This method is completely optional. If you are developing an internal console
     * command, you probably should not implement this method because it requires
     * quite a lot of work. However, if the command is meant to be used by external
     * users, this method is a nice way to fall back and prevent errors.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * This method is executed after interact() and initialize(). It usually
     * contains the logic to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('youtube-tag-start-command');

        $this->callYoutubeAPI();

        $event = $stopwatch->stop('youtube-tag-start-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New user database / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }
    }

    /**
     * @throws \Exception
     */
    protected function callYoutubeAPI()
    {
        $repositoryVideos = $this->entityManager->getRepository(VideoYoutube::class);
        $videos = $repositoryVideos->getVideosWithoutTags(self::LIMIT_VIDEOS);

        $client = new \Google_Client();
        $client->setDeveloperKey($this->keyApi);

        $youtube = new \Google_Service_YouTube($client);

        foreach ($videos as $video) {
            /** @var VideoYoutube $video */
            $this->saveVideos($youtube, $video);
        }
    }

    /**
     * @param $youtube
     * @param VideoYoutube $video
     * @throws \Exception
     */
    protected function saveVideos($youtube, VideoYoutube $video)
    {
        try{
            $responce = $youtube->videos->listVideos('id,snippet', [
                'id' => $video->getVideoId()
            ])['items'][0];

            $tags = null;
            if(is_array($responce->getSnippet()->getTags())){
                $tags = implode(',', $responce->getSnippet()->getTags());
            }

            $video->setTags($tags);

            $this->entityManager->persist($video);
            $this->entityManager->flush();
        }catch(\Exception $e){
            $this->saveErrorLog('cron-tag-youtube-log-ERROR : saveVideos -'.$e->getMessage());
        }
    }

    /**
     * Save error log
     * @param string $errorMsg
     * @throws \Exception
     */
    protected function saveErrorLog(string $errorMsg = 'ERROR')
    {
        $log = new CronLog();
        $log
            ->setName($errorMsg)
            ->setCount(0)
            ->setDatetime(new \DateTime('now'))
            ->setStatus(false);

        $this->saveLog($log);
    }

    /**
     * Save log or error log
     * @param CronLog $log
     * @throws \Exception
     */
    protected function saveLog(CronLog $log)
    {
        try{
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->saveErrorLog('cron-tag-youtube-log-ERROR : saveLog - '.$e->getMessage());
        }
    }
}
