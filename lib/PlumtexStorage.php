<?php

class PlumtexStorage
{
    private $file;
    private $storage;

    public function __construct($file = '/usr/local/cpanel/artavolo/storage/settings.json')
    {
        $this->file = $file;
    }

    public function read()
    {
        if (is_file($this->file)) {
            $data = file_get_contents($this->file);
            $this->storage = @json_decode($data, true);
        } else {
            $this->storage = '';
        }
        return $this->storage;
    }

    public function save($data)
    {
        if ($this->storage and is_array($this->storage)) {
            $data = array_merge($this->storage, $data);
        }
        $data = json_encode($data);
        return file_put_contents($this->file, $data);
    }
}