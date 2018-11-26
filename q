[1mdiff --git a/composer.json b/composer.json[m
[1mindex 4e260d3..130169b 100644[m
[1m--- a/composer.json[m
[1m+++ b/composer.json[m
[36m@@ -1,6 +1,6 @@[m
 {[m
     "name": "magicalex/rtorrent-cleaner",[m
[31m-    "description": "Script for clean useless file in rtorrent",[m
[32m+[m[32m    "description": "rtorrent cleaner script in php for remove unnecessary file in rtorrent",[m
     "keywords": ["rtorrent", "rutorrent", "cleaner", "clean", "file", "phar", "console"],[m
     "type": "library",[m
     "license": "MIT",[m
[1mdiff --git a/src/Command/RemoveCommand.php b/src/Command/RemoveCommand.php[m
[1mindex 6b00d79..45f911c 100644[m
[1m--- a/src/Command/RemoveCommand.php[m
[1m+++ b/src/Command/RemoveCommand.php[m
[36m@@ -19,16 +19,16 @@[m [mclass RemoveCommand extends Command[m
             ->setDescription('delete unnecessary files')[m
             ->setHelp('Command rm for delete unnecessary files in your download folder')[m
             ->addOption([m
[31m-                'url-xmlrcp',[m
[32m+[m[32m                'url-xmlrpc',[m
                 null,[m
                 InputOption::VALUE_REQUIRED,[m
[31m-                'set url to your scgi mount point like: http://user:pass@localhost:80/RCP',[m
[31m-                'http://localhost:80/RCP')[m
[32m+[m[32m                'Set url to your scgi mount point like: http(s)://username:password@localhost:80/RPC',[m
[32m+[m[32m                'http://rtorrent:8080/RCP')[m
             ->addOption([m
                 'home',[m
                 null,[m
                 InputOption::VALUE_REQUIRED,[m
[31m-                'set folder of your home like: /home/user/torrents',[m
[32m+[m[32m                'Set folder of your home like: /home/user/torrents',[m
                 '/data'[m
             )[m
             ->addOption([m
[36m@@ -50,9 +50,9 @@[m [mclass RemoveCommand extends Command[m
             ''[m
         ]);[m
 [m
[31m-        $list = new ListingFile($input->getOption('home'), $input->getOption('url-xmlrcp'));[m
[31m-        $dataRtorrent = $list->listing_from_rtorrent($output);[m
[31m-        $dataHome = $list->listing_from_home();[m
[32m+[m[32m        $list = new ListingFile($input->getOption('home'), $input->getOption('url-xmlrpc'));[m
[32m+[m[32m        $dataRtorrent = $list->listingFromRtorrent($output);[m
[32m+[m[32m        $dataHome = $list->listingFromHome();[m
         $notTracked = $list->getFilesNotTracked($dataHome, $dataRtorrent['path']);[m
 [m
         // remove files not tracked[m
[1mdiff --git a/src/Command/RtorrentListCommand.php b/src/Command/RtorrentListCommand.php[m
[1mindex 0989e5b..b23b745 100644[m
[1m--- a/src/Command/RtorrentListCommand.php[m
[1m+++ b/src/Command/RtorrentListCommand.php[m
[36m@@ -19,11 +19,11 @@[m [mclass RtorrentListCommand extends Command[m
             ->setDescription('create a report on unnecessary files')[m
             ->setHelp('create a report on unnecessary files')[m
             ->addOption([m
[31m-                'url-xmlrcp',[m
[32m+[m[32m                'url-xmlrpc',[m
                 null,[m
                 InputOption::VALUE_REQUIRED,[m
[31m-                'set url to your scgi mount point like: http://user:pass@localhost:80/RCP',[m
[31m-                'http://localhost:80/RCP')[m
[32m+[m[32m                'set url to your scgi mount point like: http(s)://username:password@localhost:80/RPC',[m
[32m+[m[32m                'http://rtorrent:8080/RCP')[m
             ->addOption([m
                 'home',[m
                 null,[m
[36m@@ -42,16 +42,16 @@[m [mclass RtorrentListCommand extends Command[m
             '' // empty line[m
         ]);[m
 [m
[31m-        $list = new ListingFile($input->getOption('home'), $input->getOption('url-xmlrcp'));[m
[31m-        $dataRtorrent = $list->listing_from_rtorrent($output);[m
[31m-        $dataHome = $list->listing_from_home();[m
[32m+[m[32m        $list = new ListingFile($input->getOption('home'), $input->getOption('url-xmlrpc'));[m
[32m+[m[32m        $dataRtorrent = $list->listingFromRtorrent($output);[m
[32m+[m[32m        $dataHome = $list->listingFromHome();[m
 [m
         // display torrents infos[m
         foreach ($dataRtorrent['info'] as $key => $value) {[m
             $nb = $key;[m
             $name = $value['name'];[m
[31m-            $nb_files = $value['nb_files'];[m
[31m-            $output->writeln("[{$nb}] <fg=green>Torrent:</> <fg=yellow>{$name}</> (files: <fg=yellow>{$nb_files}</>)");[m
[32m+[m[32m            $nbFiles = $value['nb_files'];[m
[32m+[m[32m            $output->writeln("[{$nb}] <fg=green>Torrent:</> <fg=yellow>{$name}</> (files: <fg=yellow>{$nbFiles}</>)");[m
 [m
             if ($output->isVerbose()) {[m
                 foreach ($value['files'] as $key => $value) {[m
[1mdiff --git a/src/Utils/ListingFile.php b/src/Utils/ListingFile.php[m
[1mindex 43c3714..0551e4e 100644[m
[1m--- a/src/Utils/ListingFile.php[m
[1m+++ b/src/Utils/ListingFile.php[m
[36m@@ -11,23 +11,23 @@[m [muse Zend\XmlRpc\Client;[m
 class ListingFile[m
 {[m
     protected $home;[m
[31m-    protected $urlXmlrcp;[m
[32m+[m[32m    protected $urlXmlRpc;[m
 [m
[31m-    public function __construct(string $home, string $urlXmlrcp)[m
[32m+[m[32m    public function __construct(string $home, string $urlXmlRpc)[m
     {[m
         $this->home = $home;[m
[31m-        $this->urlXmlrcp = $urlXmlrcp;[m
[32m+[m[32m        $this->urlXmlRpc = $urlXmlRpc;[m
     }[m
 [m
[31m-    public function listing_from_rtorrent(OutputInterface $output)[m
[32m+[m[32m    public function listingFromRtorrent(OutputInterface $output)[m
     {[m
[31m-        $progress_bar = new ProgressBar($output, 100);[m
[31m-        $rtorrent = new Client($this->urlXmlrcp);[m
[32m+[m[32m        $progressBar = new ProgressBar($output, 100);[m
[32m+[m[32m        $rtorrent = new Client($this->urlXmlRpc);[m
 [m
         $hash_torrents = $rtorrent->call('download_list');[m
         $current_torrent = 0;[m
 [m
[31m-        $progress_bar->start(); // init progress bar[m
[32m+[m[32m        $progressBar->start(); // init progress bar[m
         $total_torrents = count($hash_torrents);[m
         $number_unit_torrents = $total_torrents / 100;[m
         $number_of_torrents_expected = $number_unit_torrents;[m
[36m@@ -39,7 +39,7 @@[m [mclass ListingFile[m
 [m
             if ($current_torrent >= $number_of_torrents_expected) {[m
                 $number_of_torrents_expected = $number_of_torrents_expected + $number_unit_torrents;[m
[31m-                $progress_bar->advance(1);[m
[32m+[m[32m                $progressBar->advance(1);[m
             }[m
 [m
             $torrentInfo[$current_torrent] = [[m
[36m@@ -72,7 +72,7 @@[m [mclass ListingFile[m
             }[m
         }[m
 [m
[31m-        $progress_bar->finish();[m
[32m+[m[32m        $progressBar->finish();[m
         $output->writeln([[m
             ' <fg=green>Completed!</>',[m
             '' // empty line[m
[36m@@ -84,7 +84,7 @@[m [mclass ListingFile[m
         ];[m
     }[m
 [m
[31m-    public function listing_from_home()[m
[32m+[m[32m    public function listingFromHome()[m
     {[m
         $finder = new Finder();[m
         $finder->in($this->home)->files();[m
