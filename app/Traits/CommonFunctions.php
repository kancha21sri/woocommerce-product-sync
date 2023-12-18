<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

trait CommonFunctions
{

    /**
     * Default Image Store Directory Name
     *
     * @var string
     */
    public $defaultImageDownloadDirectory = "product_images";

    /**
     * Check weather user config the image folder or not
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed|string
     */
    public function getImageDownloadDirectoryName(){
        if(!empty(config('woocommerce_api_config.image_store_folder_name'))){
            $this->defaultImageDownloadDirectory = config('woocommerce_api_config.image_store_folder_name');
        }
        return $this->defaultImageDownloadDirectory;
    }

    /**
     * Remove the html tags given text
     *
     * @param string $string
     * @return string
     */
    public function stripTagsContent($string) {

        $string = preg_replace ('/<[^>]*>/', ' ', $string);
        $string = str_replace("\r", '', $string);
        $string = str_replace("\n", ' ', $string);
        $string = str_replace("\t", ' ', $string);
        $string = trim(preg_replace('/ {2,}/', ' ', $string));

        return $string;
    }


    /**
     * Create the Folders  in public Folder
     *
     * @param string $function_name
     * @param  mixed $arguments
     * @return bool|void
     */
    public function __call($function_name, $arguments)
    {
        $count = count($arguments);
        if ($function_name == 'createDirectory') {
            $status = false;
            try{
                if ($count == 1 ) {
                    if(empty($arguments)){
                        $arguments = $this->defaultImageDownloadDirectory;
                    }
                    if(!is_dir(public_path($arguments))){
                        mkdir(public_path($arguments), 0755);
                    }
                } else if ($count == 2) {
                    if(empty($arguments[0])){
                        $arguments[0] = $this->defaultImageDownloadDirectory;
                    }
                    if(!is_dir(public_path($arguments[0]))){
                        mkdir(public_path($arguments[0]), 0755);
                    }
                    if(!is_dir(public_path($arguments[0]."/".$arguments[1]))){
                        mkdir(public_path($arguments[0]."/".$arguments[1]), 0755);
                    }
                }

                $status = true;
            }catch(\Exception $exception) {
                $status = false;
                Log::channel('woocommerce_api')->error($exception->getMessage());
            }

            return $status;
        }
    }

    /**
     * Check given directory is empty or not
     *
     * @param string $directory_name
     * @return bool|void
     */
    public function  givenDirectoryIsEmpty($directory_name)
    {
        try {
            $handle = opendir($directory_name);
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    closedir($handle);
                    return false;
                }
            }
            closedir($handle);
            return true;

        }catch(\Exception $exception) {
            Log::channel('woocommerce_api')->error($exception->getMessage());
        }


    }

    /**
     * Generate random file name with digits
     *
     * @return string
     */
    public function generateRandomFileName()
    {
        $randomDigitWithFiveDigits = substr(str_shuffle("0123456789"), 0, 5);
        return "image_".$randomDigitWithFiveDigits;
    }

    /**
     * download images from external path
     *
     * @param string $external_path
     * @param  string $store_path
     * @return false|string
     */
    public function downloadFileFromExternalSource($external_path,$store_path)
    {
        $response = false;
        try {
            $imageName = $this->generateRandomFileName();
            $imageExtenstion = preg_replace('/^.*\.([^.]+)$/D', '$1', $external_path);
            file_put_contents($store_path."/".$imageName.".".$imageExtenstion, file_get_contents($external_path));
            if(file_exists($store_path."/".$imageName.".".$imageExtenstion)){
                $response = $imageName.".".$imageExtenstion;
            }

        }catch(\Exception $exception) {
            $response = false;
            Log::channel('woocommerce_api')->error($exception->getMessage());
        }

        return $response;

    }

    /**
     * @param string $key
     * @param array $data
     * @return array
     */
    public function groupByArray($key, $data){
        $result = array();
        foreach($data as $val) {
            if(array_key_exists($key, $val)){
                $result[$val[$key]][] = $val;
            }else{
                $result[""][] = $val;
            }
        }
        return $result;
    }

    /**
     * copy file to another location
     *
     * @param string $source_file
     * @param string $destination_file_path
     * @return void
     */
    public function copyFile($source_file,$destination_file_path)
    {
        try {
            copy($source_file, $destination_file_path);

        }catch(\Exception $exception) {
            Log::channel('woocommerce_api')->error("product image temporary copy process failed ".$exception->getMessage());

        }

    }

    /**
     * Delete Directory in public Folder
     *
     * @param string $directory_path
     * @return void
     */
    public function deleteDirectory($directory_path)
    {
        try{
            if(File::exists(public_path($directory_path))){
                File::deleteDirectory(public_path($directory_path));
            }

        }catch (\Exception $exception) {
            Log::channel('woocommerce_api')->error("duplicate image directory delete failed ".$exception->getMessage());

        }
    }

    /**
     * Rename the Directory in Public Folder
     *
     * @param string $directory_path
     * @param string $new_directory_path
     * @return void
     */
    public function renameDirectory($directory_path,$new_directory_path)
    {
        try{
            if(File::exists(public_path($directory_path))){
                rename(public_path($directory_path),public_path($new_directory_path));
            }
        }catch(\Exception $exception){
            Log::channel('woocommerce_api')->error("image directory rename process failed ".$exception->getMessage());

        }
    }
}
