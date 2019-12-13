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
class LanceYoutubeCronCommand extends Command
{
    private const MAX_RESULTS = 5;

    // to make your command lazily loaded, configure the $defaultName static property,
    // so it will be instantiated only when the command is actually called.
    protected static $defaultName = 'cron:youtube-start';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $entityManager;
    private $idYoutubeChanel;
    private $keyApi;

    public function __construct(EntityManagerInterface $em, string $idYoutubeChanel, string $keyApi)
    {
        parent::__construct();

        $this->entityManager = $em;
        $this->keyApi = $keyApi;
        $this->idYoutubeChanel = $idYoutubeChanel;
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
        $stopwatch->start('youtube-start-command');

        $this->callYoutubeAPI();

        $event = $stopwatch->stop('youtube-start-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New user database / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }
    }

    /**
     * @throws \Exception
     */
    protected function callYoutubeAPI()
    {
        $repositoryCronLog = $this->entityManager->getRepository(CronLog::class);
        $countLogs = $repositoryCronLog->countLogs();

        $params = [
            'channelId' => $this->idYoutubeChanel,
            'maxResults' => self::MAX_RESULTS,
            'order' => 'date'
        ];

        if($countLogs > 0){
            $latestLog = $repositoryCronLog->getLatestLogs();

            if(null === $latestLog){
                $this->saveErrorLog('cron-youtube-log-ERROR : callYoutubeAPI - NULL Result');
                return;
            }

            // TODO parse only frech videos without pagging
            /*if('not-nextPageToken' !== $latestLog->getNextPage()) {
                $params['pageToken'] = $latestLog->getNextPage();
            }*/
        }

        try{
            $client = new \Google_Client();
            $client->setDeveloperKey($this->keyApi);

            $youtube = new \Google_Service_YouTube($client);
            $responce = $youtube->search->listSearch('id,snippet', $params);

            // save log API Youtube
            $log = new CronLog();
            $log
                ->setName('cron-youtube-log-DONE')
                ->setCount($responce->getPageInfo()->getResultsPerPage())
                ->setDatetime(new \DateTime('now'))
                ->setNextPage($responce->getNextPageToken() ?? 'not-nextPageToken')
                ->setStatus(true);

            $this->saveLog($log);

            // save videos Youtube
            $this->saveVideos($responce['items']);

        } catch (\Exception $e) {
            $this->saveErrorLog('cron-youtube-log-ERROR : callYoutubeAPI - '.$e->getMessage());
        }
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
            $this->saveErrorLog('cron-youtube-log-ERROR : saveLog - '.$e->getMessage());
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
     * @param array $items
     * @throws \Exception
     */
    protected function saveVideos(array $items = [])
    {
        $repositoryVideo = $this->entityManager->getRepository(VideoYoutube::class);

        foreach ($items as $item){
            try{
                $videoExist = $repositoryVideo->findOneBy(['video_id' => $item['id']['videoId']]);

                if($item['id']['videoId'] && null === $videoExist){
                    $video = new VideoYoutube();
                    $video
                        ->setName(strval($item['snippet']['title']))
                        ->setDescription(strval($item['snippet']['description']))
                        ->setVideoId(strval($item['id']['videoId']))
                        ->setIsPosted(false);

                    $this->entityManager->persist($video);
                    $this->entityManager->flush();
                }
            } catch (\Exception $e){
                $this->saveErrorLog('cron-youtube-log-ERROR : saveVideos - '.$e->getMessage());
            }
        }
    }
}
