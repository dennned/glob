<?php

namespace App\Command;

use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\VideoYoutube;
use App\Utils\Slugger;
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
class LancePostCronCommand extends Command
{
    private const LIMIT_VIDEOS = 5;

    // to make your command lazily loaded, configure the $defaultName static property,
    // so it will be instantiated only when the command is actually called.
    protected static $defaultName = 'cron:youtube-post-start';

    /**
     * @var SymfonyStyle
     */
    private $io;
    private $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->entityManager = $em;
    }


    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Lance cron Post and stores them in the database')
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
        $stopwatch->start('youtube-post-start-command');

        $this->addPost();

        $event = $stopwatch->stop('youtube-post-start-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New user database / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }
    }

    /**
     * @throws \Exception
     */
    protected function addPost()
    {
        $repositoryVideos = $this->entityManager->getRepository(VideoYoutube::class);

        /** @var VideoYoutube $video */
        $video = $repositoryVideos->findOneVideoNonPosted();

        if(null !== $video){
            $post = new Post();
            $post->setTitle($video->getName());
            $post->setSlug(Slugger::slugify($video->getName()));
            $post->setSummary($video->getDescription());
            $post->setContent($video->getDescription());
            $post->setVideoId($video->getVideoId());

            $tags = explode(',', $video->getTags());
            foreach ($tags as $item) {
                $tag = new Tag();
                $tag->setName($item);

                $post->addTag($tag);
            }

            $this->entityManager->persist($post);
            $this->entityManager->flush();


//            ->setCategory()
//                ->setContent($video->getDescription())
//                ->setSummary($video->getDescription())
//                ->setVideoId($video->getVideoId())
////            ->setAuthor()
//                ->setSlug(Slugger::slugify($video->getName())
//                    ->setTitle($video->getName());

//            dump($tags);
//            dump($video);
//            dump($post);
//            die;
        }




    }
}
