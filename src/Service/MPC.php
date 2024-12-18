<?php

namespace App\Service;

use App\Entity\Station;
use App\Exception\MpcException;
use App\Exception\SystemCallException;
use Symfony\Component\HttpKernel\KernelInterface;
use Throwable;

class MPC
{

    /**
     * @var string
     */
    private readonly string $playlistFile;

    /**
     * MPC constructor.
     */
    public function __construct(KernelInterface $kernel, private readonly System $system)
    {
        $this->playlistFile =
            $kernel->getProjectDir() . DIRECTORY_SEPARATOR .
            'var' . DIRECTORY_SEPARATOR .
            'mpc-playlist.m3u';
    }

    /**
     * @throws MpcException
     * @throws SystemCallException
     * @throws Throwable
     */
    public function play(Station $station): void
    {

        # write to m3u file
        $content = $station->getUrl();
        if (false === file_put_contents($this->playlistFile, $content)) {
            throw new MpcException('Can not write playlist file');
        }

        # reload playlist
        #$this->system->call('mpc stop');
        $this->system->call('mpc clear');

        $result = $this->system->call('mpc load mpc-playlist');
        $this->system->validateOutput($result, 'loading.*mpc-playlist', MpcException::class, 'mpc load mpc-playlist failed');
        $result = $this->system->call('mpc playlist');
        $this->system->validateOutput($result, preg_quote((string) $station->getUrl()), MpcException::class, 'Can not reload mpc-playlist');

        # play first item (we have only one)
        $result = $this->system->call('mpc play 1');
        $this->system->validateOutput($result, preg_quote('[playing]'), MpcException::class, 'Can not start player');
    }

    /**
     * @throws SystemCallException
     * @throws MpcException
     */
    public function stop(): void
    {
        $this->system->call('mpc stop');
        if (!empty($this->getState())) {
            throw new MpcException('Can not stop player');
        }
        $this->system->call('mpc clearerror');
    }

    /**
     * @return string
     * @throws SystemCallException
     * @throws MpcException
     */
    public function getState(): string
    {
        # mpc current
        try {
            $result = $this->system->call('mpc current');
        } catch (SystemCallException) {
            // re-restart
            $this->startMpd();
            // try again
            $result = $this->system->call('mpc current');
        }
        if (count($result)) {
            return $result[0];
        } else {
            return '';
        }
    }

    /**
     * @return bool
     * @throws MpcException
     * @throws SystemCallException
     */
    public function isPlaying(): bool
    {
        return !empty($this->getState());
    }

    /**
     * @return int
     * @throws MpcException
     * @throws SystemCallException
     */
    public function getVolume(): int
    {
        $result = $this->system->call('mpc volume');
        if ( empty($result)) {
            throw new MpcException('empty result from: mpc volume');
        }
        if (!preg_match_all('~volume:\s*([0-9]+)%~i', (string) $result[0], $matches)) {
            throw new MpcException('can not extract volume');
        }
        return (int)$matches[1][0];
    }

    /**
     * @return $this
     * @throws MpcException
     * @throws SystemCallException
     */
    public function setVolume(int $value): self
    {
        $result = $this->system->call('mpc volume ' . $value);
        if ( empty($result)) {
            throw new MpcException('empty result from: mpc volume ' . $value);
        }

        $systemValue = null;
        foreach($result as $line) {
            if (preg_match('~volume:\s*([0-9]+)%~', (string) $line, $matches)) {
                $systemValue = $matches[1];
            }
        }
        if (null === $systemValue) {
            throw new MpcException('can not extract volume');
        }

        if ($value != (int)$systemValue) {
            throw new MpcException('could not set volume');
        }

        return $this;
    }

    /**
     * checks music-player-daemon (MPD) status
     *
     * @return bool
     * @throws MpcException
     * @throws SystemCallException
     */
    public function isMpdRunning(): bool
    {
        $result = $this->system->call('service mpd status');
        if ( empty($result)) {
            throw new MpcException('empty result from: service mpd status');
        }
/*
        $lines = array_filter($result, function ($element) {
            return preg_match('/Active:/', $element);
        });

        if (!preg_match_all('~(active|inactive)~', current($lines), $matches)) {
            throw new MpcException('can not extract daemon status');
        }

        return ($matches[1][0] === 'active');
*/
        return ($result[0] === 'mpd is running.');
    }

    /**
     * @return string
     * @throws MpcException
     * @throws SystemCallException
     */
    public function startMpd(): string
    {
        $result = $this->system->call('sudo /etc/init.d/mpd start');

        // daemon needs sometime
        $timer = 10;
        while ($timer-- > 0) {
            sleep(1);
            if ($this->isMpdRunning()) {
                break;
            }
        }

        if ( empty($result)) {
            throw new MpcException('empty result from: service mpd start');
        }

        return implode(PHP_EOL, $result);
    }

    /**
     * @return string
     * @throws SystemCallException
     */
    public function getError(): string
    {
        # mpc current
        $result = $this->system->call('mpc | grep ERROR || true');
        if (count($result)) {
            return $result[0];
        } else {
            return '';
        }
    }

    /**
     * @return false|string
     */
    public function getPlaylistFileContent(): bool|string
    {
        return file_get_contents($this->playlistFile);
    }

}
