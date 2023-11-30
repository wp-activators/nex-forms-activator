<?php

namespace NexFormsActivator;

use Exception;

/**
 * @source https://bit.ly/hk2-ptchr
 * @author moh@medhk2
 */
final class Patcher
{
    const STATUS_CAN_MODIFIED = 104;
    const STATUS_FILE_NOT_FOUND = 101;
    const STATUS_MODIFIED = 102;
    const STATUS_NOT_SUPPORTED = 103;
    const STATUS_SUCCESSFUL = 100;
    const TYPE_AFTER = 2;
    const TYPE_BEFORE = 3;
    const TYPE_REPLACE = 1;
    const CHECK_NOT = '__NOT__';
    public static $status = 0;
    protected $check;
    protected $file_path;
    protected $replace;
    protected $search;
    protected $type = self::TYPE_REPLACE;
    protected $eol = PHP_EOL;

    /**
     * @param $file_path
     */
    public function __construct($file_path)
    {
        $this->file_path = $file_path;
    }

    public function getEol(): string
    {
        return $this->eol;
    }

    public function setEol(?string $eol): Patcher
    {
        $this->eol = $eol;
        return $this;
    }

    public function resetEol(): Patcher
    {
        $this->eol = PHP_EOL;
        return $this;
    }

    /**
     * @return bool
     */
    public function canModified(): bool
    {
        if (!$this->fileExist()) return false;
        return $this->preg_match();
    }

    /**
     * @return bool
     */
    public function fileExist(): bool
    {
        return file_exists($this->file_path) && is_file($this->file_path);
    }

    /**
     * @param array|null $matches
     * @param bool $all
     * @return bool
     */
    protected function preg_match(?array &$matches = [], bool $all = false): bool
    {
        if ($all)
            return !!preg_match_all($this->search, $this->file_content(), $matches);
        else
            return !!preg_match($this->search, $this->file_content(), $matches);
    }

    /**
     * @return false|string
     */
    protected function file_content()
    {
        return $this->fileExist() ? file_get_contents($this->file_path) : false;
    }

    /**
     * @param null $backup_full_path
     *
     * @return array|false|string|string[]
     * @throws Exception
     */
    public function makeChange($backup_full_path = null, bool $multiple = false)
    {
        if (!$this->fileExist()) {
            self::$status = self::STATUS_FILE_NOT_FOUND;
            return false;
        }
        if (!$this->preg_match($matches, $multiple)) {
            self::$status = self::STATUS_NOT_SUPPORTED;
            return false;
        }
        if ($this->isModified()) {
            self::$status = self::STATUS_MODIFIED;
            return false;
        }
        $content = $this->file_content();
        $search = $matches[0];
        $new_content = $content;
        foreach ((array)$search as $match) {
            switch ($this->type) {
                case self::TYPE_REPLACE:
                    $output = $this->replace;
                    break;
                case self::TYPE_AFTER :
                    $output = $match . $this->eol . $this->replace . $this->eol;
                    break;
                case self::TYPE_BEFORE :
                    $output = $this->eol . $this->replace . $this->eol . $match;
                    break;
                default:
                    throw new Exception('Invalid type');
            }
            $new_content = str_replace($match, $output, $new_content);
        }
        self::$status = self::STATUS_SUCCESSFUL;
        if ($backup_full_path && !is_file($backup_full_path) && !is_dir($backup_full_path) && !empty($content)) {
            file_put_contents($backup_full_path, $content);
        }
        return $new_content;
    }

    /**
     * @return bool
     */
    public function isModified(): bool
    {
        if (empty($this->check) || !$this->fileExist()) return false;
        if ($this->check === self::CHECK_NOT)
            return !preg_match($this->search, $this->file_content());
        return !!preg_match($this->check, $this->file_content());
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return self::$status === self::STATUS_SUCCESSFUL;
    }

    /**
     * @param string $search
     *
     * @return $this
     * @throws Exception
     */
    public function setAfter(string $search): Patcher
    {
        $this->set_replace($search, self::TYPE_AFTER);
        return $this;
    }

    /**
     * @param string $replace
     * @param $type
     *
     * @throws Exception
     */
    protected function set_replace(string $replace, $type)
    {
        switch ($type) {
            case self::TYPE_REPLACE:
            case self::TYPE_AFTER :
            case self::TYPE_BEFORE :
                $this->replace = $replace;
                $this->type = $type;
                self::$status = 0;
                break;
            default:
                throw new Exception('Invalid type');
        }
    }

    /**
     * @param string $search
     *
     * @return $this
     * @throws Exception
     */
    public function setReplace(string $search): Patcher
    {
        $this->set_replace($search, self::TYPE_REPLACE);
        return $this;
    }

    /**
     * @param string $search
     *
     * @return $this
     * @throws Exception
     */
    public function setBefore(string $search): Patcher
    {
        $this->set_replace($search, self::TYPE_BEFORE);
        return $this;
    }

    /**
     * @param string $check
     *
     * @return self
     */
    public function setCheck(string $check): Patcher
    {
        $this->check = $check;
        return $this;
    }

    /**
     * @param string $search
     *
     * @return self
     */
    public function setSearch(string $search): Patcher
    {
        $this->search = $search;
        self::$status = 0;
        return $this;
    }
}
