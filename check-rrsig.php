<?php
/* ----------------------------------------------------------------------------
 * "THE VODKA-WARE LICENSE" (Revision 42):
 * <tim@datenkonten.me> wrote this file.  As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a vodka in return.     Tim Schumacher
 * ----------------------------------------------------------------------------
 */

require_once "vendor/autoload.php";
require_once "functions.php";


use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use \Bramus\Monolog\Formatter\ColoredLineFormatter;
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;
use Respect\Validation\Validator as v;

$getopt = new Getopt([
    new Option('r','resolver',Getopt::REQUIRED_ARGUMENT),
    new Option('d','debug',Getopt::NO_ARGUMENT),
    new Option('h','help',Getopt::NO_ARGUMENT),
]);

try {
    $getopt->parse();

    if ($getopt['help']) {
        printf($getopt->getHelpText());
    }

    // create a log channel
    $log = new Logger('console');
    $loglevel = ($getopt['debug'] ? Logger::INFO : Logger::ERROR);
    $handler = new StreamHandler('php://stdout', $loglevel);
    $handler->setFormatter(new ColoredLineFormatter());
    $log->pushHandler($handler);

    $log->addInfo('Starting the application…');

    $host = $getopt->getOperands();

    // validating the host

    if ((count($host) > 1) || (count($host) == 0)) {
        $log->addError("Please specify only one host to check.\n");       
        exit(1);
    }
    $host = trim($host[0]);
    $log->addInfo("Validating host",['host' => $host]);
    if (!(v::domain()->validate($host))) {
        $log->addError('Not a valid domain');
        exit(1);
    }

    $log->addInfo('Checking host',["host" => $host]);

    $options = ['dnssec' => true];

    if ($getopt['resolver']) {
        $options['nameservers'] = [$getopt['resolver']];
    }

    $r = new Net_DNS2_Resolver($options);

    $resolver = "";
    try {
        $resolver = getValueFromQuery($r,$host,'NS','nsdname',false);
        $log->addInfo('Got the authorative name server.',['resolver' => $resolver]);
        $log->addInfo('Get IPs for resolver');
        $resolvers = array_merge(
            getValueFromQuery($r,$resolver,'A','address'),
            getValueFromQuery($r,$resolver,'AAAA','address')
        );
        $r = new Net_DNS2_Resolver(['nameservers' => $resolvers,'dnssec' => true]);
        $log->addInfo('Fetching RRSIG from original server to bypass cache.',["resolvers" => $resolvers]);
        $expire_date = getValueFromQuery($r,$host,'RRSIG','sigexp',false);
        $now = new \DateTime();
        $expire_date = \DateTime::createFromFormat("YmdHis",$expire_date);
        $log->addInfo("Calculate the datediff",['expire_date' => $expire_date->format('c')]);
        $diff = intval($now->diff($expire_date)->format('%R%a'));
        echo $diff . "\n";
    } catch(Net_DNS2_Exception $e) {
        $log->addError('Query Failed',['exception' => $e->getMessage()]);
    }

    exit(0);   
} catch (UnexpectedValueException $e) {
    $log->addError($e->getMessage());
    echo $getopt->getHelpText();
    exit(1);
}