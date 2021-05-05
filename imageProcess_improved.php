<?php

//connection delay model
//sleep(1.2);

//root directiory declaration
$httpRoot = 'D:/HTTP_WEB/realfeedserver/';
define('httpRoot',$httpRoot);

// STRUCTURED FUNCTION SET 
require $httpRoot . "/sfs.php";



//simple image library
require_once $httpRoot . "/php.api/SimpleImage.php";

//shortcut function to show a image in html
function showImg($fileName)
{
    echo '<div class="imgBox"><div class="imageName">'.  $fileName . '</div><img src="http://www.realfeed.com/imgPro/'.$fileName.'"/></div>';
}

function simplify($obj,$sensitivity)
{

    $simplified = $obj->resize($sensitivity,$sensitivity);
    //making the bightness array :array1 goes top to bottom
    $dataArray1 = [];
    for($x=0;$x<$sensitivity;$x++)
    {
        for($y=0;$y<$sensitivity;$y++)
        {
            $color = $simplified->getColorAt($x,$y);
            $r = intval($color['red']);
            $g = intval($color['green']);
            $b = intval($color['blue']);
            
            $colorArray = [$r,$g,$b];
            
           array_push($dataArray1,$colorArray);
        }
    }
    
    
    return $dataArray1;
}


function similarity($array1,$array2,$off=5)
{
    if(count($array1) != count($array2)) { return -1;}
    
    $boxCount = count($array1);
    $similarity =$boxCount;

    for($i=0;$i<$boxCount;$i++)
    {
        $box1 = $array1[$i];
        $box2 = $array2[$i];
        if(abs($box1[0]-$box2[0])>$off ||abs($box1[1]-$box2[1])>$off ||abs($box1[2]-$box2[2])>$off)
        {
            $similarity --;
        }
    }
    return $similarity/$boxCount;
}

/*
$GLOBALS['simplifiedPhotoData'] =[];

$photoBD = [];
$photoDB['table'] = $httpRoot . 'photo.manager/db/posts.sfs';
$photoDB['function'] = function($post)
                    {
                        $photoSet = json_decode($post['imageids'],true);
                        foreach ($photoSet as $photo)
                        {
                            $thisImg = $photo['photoid'];
                            $image = new \claviska\SimpleImage();
                            $image->fromfile(httpRoot . '/photo.manager/' . $thisImg . '.jpeg');
                            $simpImg = simplify($image,4);
                            array_push($GLOBALS['simplifiedPhotoData'],[$thisImg,$simpImg]);
                        }
                    };
runsfs($photoDB);

$simplifiedPhotoDataText = json_encode($GLOBALS['simplifiedPhotoData']);

$fo = fopen(httpRoot . 'photo.manager/db/simplified.txt','w');
fwrite($fo,$simplifiedPhotoDataText);
fclose($fo);

echo 'done';
*/


/*
$fo = fopen(httpRoot . 'photo.manager/db/simplified.txt','r');
$content = fread($fo,filesize(httpRoot . 'photo.manager/db/simplified.txt'));
fclose($fo);

$photos = json_decode($content,true);//array of [photo_id,key]
shuffle($photos);
$classified =[];

for($i = 0 ;$i<count($photos);$i++)
{
    $photo1 = $photos[$i];
    for($u=$i+1;$u<count($photos);$u++)
    {
        $photo2 = $photos[$u];

        //if match
        if(similarity($photo1[1],$photo2[1],30)>=0.8)
        {
            if(isset($classified[$photo1[0]]))
            {
                if(gettype($classified[$photo1[0]])=='array')
                {
                    array_push($classified[$photo1[0]],$photo2[0]);
                    $classified[$photo2[0]] = $photo1[0];
                }
                else//string
                {
                    array_push($classified[$classified[$photo1[0]]],$photo2[0]);
                    $classified[$photo2[0]] = $classified[$photo1[0]];
                }
            }
            else if(isset($classified[$photo2[0]]))
            {
                array_push($classified[$classified[$photo2[0]]],$photo1[0]);
                $classified[$photo1[0]] = $classified[$photo2[0]];

            }
            else
            {
                $classified[$photo1[0]] = [$photo1[0],$photo2[0]];
                $classified[$photo2[0]] = $photo1[0];
            }
        }


    }
}

//classified is ready---save
$fo = fopen(httpRoot . 'photo.manager/db/classified.txt','w');
fwrite($fo,json_encode($classified));
fclose($fo);

foreach($classified as $class)
{
    if(gettype($class) == 'array')
    {
        foreach($class as $img)
        {
            echo '<img src="http://www.realfeed.com/photo.manager/' .$img. '.jpeg" style="max-width:500px;"/><br/>';
        }
        echo '<div style="background-color:#000000;height:10px;"></div>';
    }
}






echo 'done';

*/





/*
echo 'started<br/>';


$GLOBALS['img_path_names'] =[];

$photoBD = [];
$photoDB['table'] = $httpRoot . 'photo.manager/db/posts.sfs';
$photoDB['function'] = function($post)
                    {
                        $photoSet = json_decode($post['imageids'],true);
                        foreach ($photoSet as $photo)
                        {
                            $thisImg = $photo['photoid'];
                            //$image = new \claviska\SimpleImage();
                            //$image->fromfile(httpRoot . '/photo.manager/' . $thisImg . '.jpeg');
                            //$simpImg = simplify($image,4);
                            array_push($GLOBALS['img_path_names'],$thisImg);
                        }
                    };
runsfs($photoDB);

$res= json_encode($GLOBALS['img_path_names']);
$outputFile = fopen(httpRoot . '/demo/photopaths.txt','w');
fwrite($outputFile,$res);
fclose($outputFile);
echo count($GLOBALS['img_path_names']);

*/

if(($_GET['simplify']??0) =='1')
{
    $timeStart = microtime(true);

    $totalTime_fileOpen = 0;
    $totalTime_imageSimplify = 0;

    $GLOBALS['simplifiedPhotoData']  = [];
    $imagePathFile = fopen(httpRoot . '/demo/photopaths.txt','r');
    $fileNames = json_decode(fread($imagePathFile,filesize(httpRoot . '/demo/photopaths.txt')),true);
    for($i=0;$i<count($fileNames);$i++)
    {
        $fileName = $fileNames[$i];

        $image = new \claviska\SimpleImage();
        $time1 = microtime(true);
            $image->fromfile(httpRoot . '/photo.manager/' . $fileName . '.jpeg');
        $time2 = microtime(true);
            $simpImg = simplify($image,5);
        $time3 = microtime(true);
        array_push($GLOBALS['simplifiedPhotoData'],[$fileName,$simpImg]);

        //time cals
        $totalTime_fileOpen += ($time2 - $time1);
        $totalTime_imageSimplify += ($time3 - $time2);


    }

    $dataToSave = json_encode($GLOBALS['simplifiedPhotoData']);

    //saving
    $fileToSave = fopen(httpRoot . '/demo/simplifiedImageData.json','w');
    fwrite($fileToSave,$dataToSave);
    fclose($fileToSave);

    $timeEnd = microtime(true);

    echo 'Total time taken: ' . strval($timeEnd -$timeStart) . 's' . br . hr;
    echo 'time to open files: ' . $totalTime_fileOpen .'s' . br;
    echo 'time to simplify images: ' . $totalTime_imageSimplify .'s' . br;
    echo 'time for other things: ' . strval($timeEnd -$timeStart - $totalTime_fileOpen - $totalTime_imageSimplify) . 's' . br;
}


if (($_GET['search']??-1) !=-1) 
{
    $filesInImageSet = scandir(httpRoot . 'demo/imageSet');
    $fileData = fopen(httpRoot . '/demo/simplifiedImageData.json', 'r');
    $imageData = json_decode(fread($fileData, filesize(httpRoot . '/demo/simplifiedImageData.json')), true);
    fclose($fileData);

    foreach ($filesInImageSet as $fileName) {
        if ($fileName != '.' && $fileName != '..') 
        {

            echo 'Searching images for' . br.br;
            echo '<img src="http://www.realfeed.com/demo/imageSet/' .$fileName. '" style="max-width:500px;border:solid 3px #000000"/>' . br . br;

            $serachQueryImg = new \claviska\SimpleImage();
            $serachQueryImg -> fromFile(httpRoot . '/demo/imageSet/' . $fileName);
            $simplifiedSQI = simplify($serachQueryImg, 5);

            for ($i=0;$i<count($imageData);$i++) {
                $thisDataImage = $imageData[$i];
                $thisImageName = $thisDataImage[0];
                $thisData = $thisDataImage[1];


                $similarityFrac = similarity($simplifiedSQI, $thisData, intval($_GET['search']??0));
                
                if ($similarityFrac > 0.7) {
                    echo '<img src="http://www.realfeed.com/photo.manager/' .$thisImageName. '.jpeg" style="max-width:500px;"/>'.br.br;
                }
                else if($similarityFrac == -1)
                {
                    echo 'BIG ERROR | box counts does not match from data set to images' . br;exit();
                }
            }

            echo hr;
        }
    }
}





?>