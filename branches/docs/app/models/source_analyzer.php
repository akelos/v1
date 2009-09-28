<?php

Ak::import('component,file,method,parameter,categories,source_parser,akelos_class');

class SourceAnalyzer
{
    var $dir = AK_LIB_DIR;
    var $base_dir = AK_FRAMEWORK_DIR;

    function analyze($file_or_dir)
    {
        $path = $this->base_dir.DS.$file_or_dir;
        if(is_file($path)){
            $this->storeFileForIndexing($file_or_dir);
            $this->indexFile($file_or_dir);
        }elseif (is_dir($path)){
            $this->storeFilesForIndexing($this->base_dir.DS.$file_or_dir);
            $this->indexFiles();
        }else{
            trigger_error('Could not find '.$path);
        }
    }

    function storeFilesForIndexing($dir = null)
    {
        $files = $this->getSourceFileDetails($dir);
        foreach ($files as $k=>$filename){
            $this->storeFileForIndexing($filename);
        }
    }

    function storeFileForIndexing($file_path)
    {
        $FileInstance = new File();
        $file_details = array(
        'path' => $file_path,
        'body' => file_get_contents($this->base_dir.DS.$file_path),
        'hash' => md5_file($this->base_dir.DS.$file_path),
        'has_been_analyzed' => false
        );
        if($SourceFile =& $FileInstance->findFirstBy('path', $file_path)){
            if(!$file_details['has_been_analyzed']){
                $this->log('File '.$file_details['path'].' is stored but not indexed.');
            }
            $file_details['has_been_analyzed'] = $SourceFile->hash == $file_details['hash'] && $SourceFile->get('has_been_analyzed');
            if(!$file_details['has_been_analyzed']){
                $this->log('File '.$file_details['path'].' marked for reanalizing');
                $SourceFile->setAttributes($file_details);
                $SourceFile->save();
            }else{
                $this->log('File '.$file_details['path'].' is up to date');
            }

        }else{
            $this->log('Storing file '.$file_details['path']);
            $SourceFile = new File($file_details);
            $SourceFile->save();
        }
    }

    function getSourceFileDetails($dir = null)
    {
        static $dir_cache;
        $this->dir = empty($dir) ? $this->dir : $dir;
        if(!isset($dir_cache[$this->dir])){
            $dir_cache[$this->dir] = $this->_transverseDir($this->dir);
        }

        return $dir_cache[$this->dir];
    }


    function _transverseDir($path)
    {
        $this->log('Transversing directory '.$path);

        $result = array();

        $path = rtrim($path, '/\\');
        if(is_file($path)){
            $result = array($path);
        }elseif(is_dir($path)){
            if ($id_dir = opendir($path)){
                while (false !== ($file = readdir($id_dir))){
                    if ($file != "." && $file != ".." && $file != '.svn'){
                        if(!is_dir($path.DS.$file)){
                            $result[md5_file($path.DS.$file)] = ltrim(str_replace($this->base_dir, '', $path.DS.$file), '/');
                        }else{
                            $result = array_merge($result, $this->_transverseDir($path.DS.$file));
                        }
                    }
                }
                closedir($id_dir);
            }
        }

        return array_reverse($result);
    }

    function importCategories($categories)
    {
        Ak::import('category');
        $CategoryInstance =& new Category();
        foreach ($categories as $category_name=>$related){
            if(!$Category =& $CategoryInstance->findFirstBy('name', $category_name)){
                $Category =& new Category(array('name'=>$category_name));
                if($Category->save()){
                    $this->log('Created new category: '.$category_name);
                }
            }
            if(!empty($related['relations'])){
                foreach ($related['relations'] as $related_category){
                    if(!$RelatedCategory =& $CategoryInstance->findFirstBy('name', $related_category)){
                        $RelatedCategory =& new Category(array('name'=>$related_category));
                        $RelatedCategory->save();
                    }
                    $this->log('Relating category '.$related_category.' with '.$category_name);
                    $Category->related_category->add($RelatedCategory);
                }
            }
        }
    }

    function getFileDetails($file_contents)
    {
        $parsed = $this->getParsedArray($file_contents);
        return $parsed['details'];
    }

    function getParsedArray($file_contents)
    {
        static $current;
        $k = md5($file_contents);
        if(!isset($current[$k])){
            $current = array();
            $SourceParser =& new SourceParser($file_contents);
            $current[$k] = $SourceParser->parse();
        }
        return $current[$k];
    }

    function indexFiles()
    {
        $FileInstance =& new File();
        if($UnIndexedPages =& $FileInstance->findAllBy('has_been_analyzed', false)){

            $ComponentInstance =& new Component();
            $ClassInstance =& new AkelosClass();

            foreach (array_keys($UnIndexedPages) as $k){
                $this->log('Analyzing file '.$UnIndexedPages[$k]->path);

                $Component =& $ComponentInstance->updateComponentDetails($UnIndexedPages[$k], $this);
                $Classes =& $ClassInstance->updateClassDetails($UnIndexedPages[$k], $Component, $this);

                if(!empty($Classes)){
                    //Ak::debug($Classes);
                }

                $UnIndexedPages[$k]->set('has_been_analyzed', true);
                $UnIndexedPages[$k]->save();
            }
        }
    }

    function indexFile($file_path)
    {
        $file_path = trim($file_path, '/');
        $FileInstance =& new File();
        if($UnIndexedPage =& $FileInstance->findFirstBy('path AND has_been_analyzed', $file_path, false)){
            $ComponentInstance =& new Component();
            $ClassInstance =& new AkelosClass();
            $this->log('Analyzing file '.$UnIndexedPage->path);

            $Component =& $ComponentInstance->updateComponentDetails($UnIndexedPage, $this);
            $Classes =& $ClassInstance->updateClassDetails($UnIndexedPage, $Component, $this);

            if(!empty($Classes)){
                //Ak::debug($Classes);
            }

            $UnIndexedPage->set('has_been_analyzed', true);
            $UnIndexedPage->save();
        }
    }

    function log($message)
    {
        echo " $message\n";
    }

}

function number_to_human_size($size, $decimal = 1)
{
    if(is_numeric($size )){
        $position = 0;
        $units = array( ' Bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
        while( $size >= 1024 && ( $size / 1024 ) >= 1 ) {
            $size /= 1024;
            $position++;
        }
        return round( $size, $decimal ) . $units[$position];
    }else {
        return '0 Bytes';
    }
}

?>