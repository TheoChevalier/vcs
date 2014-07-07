<?php
namespace VCS;

use DateTime;

class Base
{
    const VERSION = '1.0dev';
    public $repositoryPath;

    public function __construct($repositoryPath)
    {
        $this->repositoryPath = realpath($repositoryPath);
    }


    public function parseLog($log)
    {
        for ($i = 0, $lines = count($log); $i < $lines; $i++) {
            $tmp = explode(': ', $log[$i]);
            $tmp = array_map('trim', $tmp);

            if ($tmp[0] == 'changeset') {
                $commit = trim($tmp[1]);
            }

            if ($tmp[0] == 'user') {
                if (! strstr($tmp[1], '@')) {
                // No email in User field
                    $author = $tmp[1];
                    $email = 'Unknown';
                } elseif (preg_match('~<([:alpha]*.+)>~', $tmp[1], $matches)) {
                // John Doe <john@doe.com>
                    $email = str_replace(['<', '>'], '', $matches[0]);
                    $author = explode('<', $tmp[1])[0];
                } elseif (preg_match('~\(([:alpha]*.+)\)~', $tmp[1], $matches)) {
                // John Doe (john@doe.com)
                    $email = str_replace(['(', ')'], '', $matches[0]);
                    $author = explode('(', $tmp[1])[0];
                } elseif (preg_match('~([:alpha]*.+)~', $tmp[1], $matches)) {
                // John Doe john@doe.com
                    $email = $matches[0];
                    $author = str_replace($matches[0], '', $tmp[1]);

                // john@doe.com
                    if ($author == '') {
                        $author = $email;
                    }
                } else {
                // Fallback
                    $email  = 'Unknown';
                    $author = 'Unknown';
                }

                $email = trim($email);
                $author = trim($author);
            }

            if ($tmp[0] == 'date') {
                $date = trim($tmp[1]);
            }

            if ($tmp[0] == 'summary') {
                $summary = trim($tmp[1]);

                $commits[] = [
                    'commit'  => $commit,
                    'author'  => $author,
                    'email'   => $email,
                    'date'    => DateTime::createFromFormat('D M j H:i:s Y O', $date),
                    'summary' => $summary
                ];
            }
        }

        return $commits;
    }

    protected function execute($command)
    {
        $cwd = getcwd();
        chdir($this->repositoryPath);
        exec($command, $output, $returnCode);
        chdir($cwd);

        if ($returnCode !== 0) {
            error_log("Error with command {$command} launched in {$this->repositoryPath}");
        }

        return $output;
    }
}
