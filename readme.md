# KerogsPHP

## Installation

```sh
composer require kerogs/kerogs-php
```

### How to use

To use the package, you first need to import the autoload.php file from the vendor folder.

Once you've done that, you can use only what you're interested in.

```php
require_once '/vendor/autoload.php';

use Kerogs\KerogsPhp\[name];
```

> [!NOTE]
> Replace `[name]` with the name of what you want to use. You can refer to the list below.

## List
> [!NOTE]
> All requested encryption keys are in AES-256-CBC format 

### Kerogs\KerogsPhp\Key

The `Key()` class is used to generate unique keys.

#### Example of use

```php
require_once '/vendor/autoload.php';

use Kerogs\KerogsPhp\Key;
$key = new Key(1);

echo $key->keyGeneration(16);
```

When initializing a key, you must specify the type of character string you will use. This will be represented by a number.

```php
    private static $keyTypes = [
        1 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )'!@#$%^&*",
        2 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )'!",
        3 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-( )",
        4 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-",
        5 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        6 => "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ",
        7 => "abcdefghijklmnopqrstuvwxyz",
        8 => "0123456789",
        9 => "abcdefghijklmnopqrstuvwxyz0123456789"
    ];
```

#### Function list

|function|description|
|--------|-----------|
|``keyGeneration(int $length): string``|Generates a random key based on the character set associated with the type of key.|
|``getCharacterSet(): string``|Gets the character set for the given type of key.|

### Kerogs\KerogsPhp\Logs
You can easily create your own logs and, if required, encrypt them.
#### Example of use
```php
require_once '/vendor/autoload.php';

use Kerogs\KerogsPhp\Logs;

const LogsKey = 'abcd-efgh-ijkl-mnop';
$logs = new Logs(LogsKey, true);

$logs->addLog(null, "User successfully logged in", 200, "INFO", false, true);
```

In the example, we initialize the class by specifying a key (this key will be used to encrypt and decrypt the contents of the logs if required). We then indicate via a ``true`` that we wish to encrypt the log file.

Then we simply add the logs.

> [!IMPORTANT]
> The log key must be in 16-byte format, as in. 

If we specify no path for the logs by indicating ``null`` for the addLog() function, then a file will automatically be created at the server root and named ``kp_server.log``.

Also worth knowing:
- When the file is encrypted, it will appear under the name ``[fileName].log.kpc``.
- When the file is decrypted, it will appear as ``[fileName].log``.

#### Function list
##### addLog()
Add a log entry to the file.
```php
addLog(
    string $pathLogs = null,
    string $message = "-",
    int $statusCode = 200,
    string $logType = "INFO",
    bool $logIp = false,
    bool $logRequestData = false
):
```

##### encryptDecryptFile()
Encrypt or decrypt a log file.
```php
encryptDecryptFile(string $filePath, bool $encrypt): void
```

### Kerogs\KerogsPhp\Sendmail
You can send your e-mails directly without rewriting the code.

#### Example of use
```php
require_once __DIR__.'/vendor/autoload.php';

use Kerogs\KerogsPhp\Sendmail;

const from = "froms@example.com";
const to = "to@example.com";

$sendmail = new Sendmail(from);

if($sendmail->sendMail(to, "Subject test", "hello world")))
    echo "Email sent successfully";
else
    echo "Email not sent";
```
The default content type is ``text/HTML``

### Kerogs\KerogsPhp\Github
allows you to retrieve information from a GITHUB repository and compare versions.

#### Function list

|function|description|
|--------|-----------|
|``Retrieves all repository info (JSON format)``|Retrieves all repository info (JSON format)|
|``getLatestRelease($owner, $repo, $onlyName = true)``|Retrieves only the name of the latest repository version (or all information on the latest release).|
|``compareVersions($versionActual, $versionLatest)``|Compares 2 versions (not only works for GITHUB) (its format must be X/X.Y/X.Y.Z/X.Y.Z.F, if there is content after a “-” it will not be taken into account). (will return ``true`` if same version (if not, will return ``above`` or ``below`` the current version.))|

#### Example of use
```php
require_once __DIR__.'/vendor/autoload.php';

use Kerogs\KerogsPhp\Github;

$github = new Github();

$lastRelease = $github->compareVersions("1.3.17", $github->getLatestRelease("KSLaboratories", "kerogsPHP", false)['name']);

if($lastRelease['same']) {
    echo "KerogsPHP is up to date !";
} else{
    if($lastRelease['comparison'] === 'above') {
        echo "KerogsPHP is outdated ! (above)";
    } else {
        echo "KerogsPHP is outdated ! (below)";
    }
}
```

### Kerogs\KerogsPhp\Algorithm
Allows you to manage an algorythm

#### Function list

|function|description|
|--------|-----------|
|``similarityPercentage($str1, $str2)``|Returns the similarity percentage|
|``searchEngine(array $values, $query)``|Returns an array of values. Sort from most similar to least similar|

#### Example of use
```php
require_once __DIR__.'/vendor/autoload.php';

use Kerogs\KerogsPhp\Algorithm;

$algo = new Algorithm();

$searchBanana = $algo->searchEngine(['banana', 'apple', 'orange', 'pineapple'], 'banana');

print_r($searchBanana);
```