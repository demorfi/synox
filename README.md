# Synox
-------
Unofficial synology plugins repository.

Attention!
========
This project is marked as archived. 
Use an alternative in the form of [SynoX Web](https://github.com/demorfi/synox-web) 
and [Synology SynoX Web Plugins](https://github.com/demorfi/synology-synox-web-plugins)

Required
========
* DSM 5.1

Includes
========
###### Audio Station Modules
* Bananan *(Translate song text into Russian Language to http://bananan.org)*
* LyricWiki *(Song full-text search to http://lyrics.wikia.com)*

###### Download Station Search Modules
* Fast-Torrent *(Search torrent files to http://fast-torrent.ru)*
* Kinozal *(Search torrent files to http://kinozal.tv)*
* NoNaMe-Club *(Search torrent files to http://nnm-club.me)*
* Pornolab *(Search torrent files to http://pornolab.net)*
* Rutor *(Search torrent files to http://rutor.org)*
* Rutracker *(Search torrent files to http://rutracker.org)*
* The Pirate Bay *(Search torrent files to http://thepiratebay.se)*
* YouTube *(Search broadcast to http://youtube.com)*

###### Download Station Host Modules
* Kinozal *(Download torrent files to http://kinozal.tv)*
* NoNaMe-Club *(Download torrent files to http://nnm-club.me)*
* Pornolab *(Download torrent files to http://pornolab.net)*
* Rutracker *(Download torrent files to http://rutracker.org)*

HOWTO Use
=========
1. Build modules to get tar.gz files 
2. Login to you Synology with admin privileges
3. Open Download Station or Audio Station package
4. Go to Settings area

###### For Download Station Search Module
1. Go to File Search for Do, found on left hand side
2. Click add and locate required **~/synox/builds/*.dlm** file
3. Once done click edit and add your account details

###### For Download Station Host Module
1. Go to File Hosting for Do, found on left hand side
2. Click add and locate required **~/synox/builds/*.host** file
3. Once done click edit and add your account details

###### For Audio Station
1. Go to Plugins Text for Do, found on top hand side
2. Click add and locate **~/synox/builds/*.aum**
3. Move plugins in the list to change their priority use

Build
=====
```bash
cd ~ && git clone https://github.com/demorfi/synox.git synox && cd synox
make && ls builds -lX

# rebuild
make clean && make && ls builds -lX
```

or use tar gz command in directory src.

Debug
=====
#### Download Station Search Module
```bash
cd ~ && git clone https://github.com/demorfi/synox.git synox && cd synox
php syno.php bt "module name" "search query" ["username"] [:"password"]

# example
php syno.php bt rutracker "Silent Hill" "test" "123pwd"
```

#### Download Station Host Module
```bash
cd ~ && git clone https://github.com/demorfi/synox.git synox
cd synox
php syno.php ht "module name" "url torrent file" ["username"] [:"password"]

# example
php syno.php ht rutracker "http://dl.rutracker.org/forum/dl.php?t=9999999" "test" "123pwd"
```

#### Audio Station Lyrics Module
```bash
cd ~ && git clone https://github.com/demorfi/synox.git synox
cd synox
php syno.php au "module name" "artist song" "title song"

# example
php syno.php au lyricwiki "30 Seconds To Mars" "93 Million Miles"
```

#### Debug working module
1. Enable debug mode **(use [opt:])**
2. Read log file /tmp/(**ht-**,**bt-**)module-name.log

Tips and tricks
===============
* Use a special search request **[opt:]query** for advanced search
* Use as needed option in the account name **[opt:]username** for advanced mode
* Use sort on domain name after searching

Available options [opt:]
===============
* h-host ***(module name for single search, use only search toolbar)***
* p-page ***(max page search)***
* d-1 ***(use debug mode)***

#### Example
* [opt:h-rutracker]In Time ***(for only search)***
* [opt:p-2,h-rutracker]In Time ***(for search only host and max search page)***
* [opt:d-1,h-rutracker]In Time ***(for enable debug mode)***
* [opt:p-2]In Time ***(max search page)***

Change Log
==========
v1.0 - Feb 26, 2015
--------------------
 * Initialize repository version 1.0

License
=======
Synox is licensed under the [MIT License](http://www.opensource.org/licenses/mit-license.php).
