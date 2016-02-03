# udemy-dl

Udemy Downloader.

### Features
  - Downloads Videos, E-books, Articles, Audios, Presentations, and Extras (no Quizzes yet)
  - Resume downloading of course midway

### Version
1.0

### Pre-requisites

**wget**

Windows users can get the pre-built `wget` binary from [Eternally bored][eb]. Paste the binary in the folder containing udemy-dl.  
Linux users can install `wget` from their package manager
```sh
$ apt-get install wget
```

### Usage

Download the PHP files from the *src* folder and run the following in cmd.
```sh
$ php udemy-dl.php [OPTIONS]
```

### Options

```text
-h, --help              Print this help text and exit
-i, --id ID             Course ID of the course to be downloaded
-u, --url URL           URL of the course to be downloaded (Ingored if course ID is specified)
-s, --start INDEX       Starts downloading directly from file #INDEX
-e, --end INDEX         Stops downloading after file #INDEX
-l, --list I1, I2...    Downloads all files with indexes specified in the list
-f, --folder FOLDER     Specifies the folder where files will be downloaded.
                        (Default is the current working directory)
-d, --downloader NAME   Specifies the download manager to be used. (Default is wget)
                        Options: wget, idm (Internet Download Manager)
```

### Examples

Download a course using the URL
```sh
$ php udemy-dl.php -u https://www.udemy.com/example-course/
```

Download a course using IDM as the downloader
```sh
$ php udemy-dl.php -u https://www.udemy.com/example-course/ -d idm
```

Download course from lecture #25 using course ID to a custom directory
```sh
$ php udemy-dl.php -i 9513431 -s 25 -f E:\Courses\
```

### Todos

 - Add a switch for download quality
 - Add support for quizzes

**Free Software, Hell Yeah!**

   [eb]: <https://eternallybored.org/misc/wget/>

