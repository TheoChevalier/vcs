<?php
namespace VCS;

class Mercurial extends Base
{
    public function getCommits()
    {
        $log = $this->execute('hg log');

        return $this->parseLog($log);
    }

    public function update()
    {
        $this->execute('hg pull -r default');
        $this->execute('hg update -C');

    }
}
