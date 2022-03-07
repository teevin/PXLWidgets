<?php

namespace App\Custom;

use App\Interfaces\FileHandlerInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;

class Json implements FileHandlerInterface
{
    /**
     * default import file
     */
    const importFile = "challenge.json";
    /**
     * @var Json
     */
    protected $fileContents;
    /**
     * @var Json
     */
    protected $filePath;
    /**
     * @var Json
     */
    protected $file;

    /**
     * @param $filepath
     */
    public function __construct($filepath = null)
    {
        $this->filePath = $filepath;
    }
    /**
     * @inheritDoc
     */
    public function process(): array
    {
       if($this->filePath && Storage::disk('public')->exists($this->filePath)) {
           $this->file = Storage::disk("local")->get($this->filePath);
       } elseif(Storage::disk('public')->exists(self::importFile)){
           $this->file = Storage::disk("public")->get(self::importFile);
       }
       if(!$this->file) {
           throw new FileNotFoundException('no valid import file found');
       }
       $this->file = json_decode($this->file,false);
       return $this->file;

    }
}
