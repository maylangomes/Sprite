<?php

function iterative(&$folder, $files = [])
{
    if (isset($folder)) {
        if (is_dir($folder)) {
            $ressource = opendir($folder);
            while (($file = readdir($ressource)) != false) {
                if ($file != "." && $file != "..") {
                    $path = $folder . DIRECTORY_SEPARATOR . $file;
                    if (pathinfo($path, PATHINFO_EXTENSION) == 'png') {
                        $files[] = $path;
                    }
                }
            }
        } else {
            echo "Error : '$folder' isn't a folder" . PHP_EOL;
        }
    } else {
        echo "Error : folder doesn't exist" . PHP_EOL;
    }
    return $files;
}

function recursive($folder, $files = [])
{
    if (isset($folder)) {
        if (is_dir($folder)) {
            $ressource = opendir($folder);
            while (($file = readdir($ressource)) !== false) {
                if ($file !== "." && $file !== "..") {
                    $path = $folder . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($path)) {
                        $recursive = recursive($path, $files);
                        $files = array_merge($files, $recursive);
                    } else {
                        if (pathinfo($path, PATHINFO_EXTENSION) == 'png') {
                            $files[] = $path;
                        }
                    }
                }
            }
            closedir($ressource);
        } else {
            echo "Error : '$folder' isn't a folder" . PHP_EOL;
        }
    } else {
        echo "Error : folder doesn't exist" . PHP_EOL;
    }
    $files = array_unique($files);
    return $files;
}

function spriter($files, $dest = 'sprite.png', $css_name = 'style.css', $padding = 0)
{
    $width = 0;
    $height = 0;
    $images = array();
    $css_lign = [];
    $temp = [];
    if (isset($files)) {
        if (is_array($files)) {
            foreach ($files as $file) {
                list($w, $h, $t) = getimagesize($file);
                $width += $w;
                $height = max($height, $h);
                $images[] = array('file' => $file, 'type' => $t);
                foreach ($images as $img_css) {
                    if (!in_array($img_css['file'], $temp)) {
                        $temp[] = $img_css['file'];
                    }
                }
            }
        }
    }
    $i = 0;
    $pos = 0;
    foreach ($temp as $key => $img_temp) {
        while ($i < count($temp)) {
            list($w, $h, $t) = getimagesize($temp[$i]);
            $name = basename($temp[$i]);
            $name = basename($name, ".png");
            $css_lign[] = ".$name {" . PHP_EOL . "\tbackground-position : -$pos"
                 . "px -0px;" . PHP_EOL
                  . "\theight : $h;" . PHP_EOL
                   . "\twidth : $w;" . PHP_EOL . "}" . PHP_EOL;
            $pos += ($padding + $w);
            $i++;
        }
    }
    

    if (isset($css_lign)) {
        file_put_contents($css_name, $css_lign);
    } else {
        echo "Error : Nothing to write into the file" . PHP_EOL;
    }
    if ($width != 0 && $height != 0) {
        $img = imagecreatetruecolor($width, $height);
        $background = imagecolorallocatealpha($img, 1, 1, 1, 127);
        imagefill($img, 0, 0, $background);
        imagesavealpha($img, true);
    } else {
        echo "Error : Can't create image if width or height egal 0" . PHP_EOL;
    }

    $pos = 0;
    foreach ($images as $one_img) {
        if ($one_img['type'] === 3) {
            $img_tmp = imagecreatefrompng($one_img['file']);
            list($w, $h, $t) = getimagesize($one_img['file']);
            imagecopy($img, $img_tmp, $pos, 0, 0, 0, $w, $h);
            $pos += ($padding + $w);
            imagedestroy($img_tmp);
        }
    }
    if (isset($img)) {
        imagepng($img, $dest);
    }
}

array_shift($argv);

if (isset($argv[0])) {
    if ($argc < 4) {
        if ($argc === 2) {
            $folder = $argv[0];
            $folder = iterative($folder);
            spriter($folder);
        } elseif ($argv[0] === "-r" || $argv[0] === "--recursive") {
            $folder = $argv[1];
            $folder = recursive($folder);
            spriter($folder);
        } elseif ($argv[0] === "-i" || $argv[0] === "--output-image" || $argv[0] === "-s" || $argv[0] === "--output-style") {
            $folder = $argv[1];
            $folder = iterative($folder);
            spriter($folder);
        } elseif (strpos($argv[0], "--output-image=") === 0 || strpos($argv[0], "-i=") === 0) {
            $name_img = $argv[0];
            $name_img = strstr($name_img, '=');
            $name_img = substr($name_img, 1);
            $name_img = $name_img . ".png";
            $folder = $argv[1];
            $folder = iterative($folder);
            spriter($folder, $name_img);
        } elseif (strpos($argv[0], "--output-style=") === 0 || strpos($argv[0], "-s=") === 0) {
            $name_css = $argv[0];
            $name_css = strstr($name_css, '=');
            $name_css = substr($name_css, 1);
            $name_css = $name_css . ".css";
            $folder = $argv[1];
            $folder = iterative($folder);
            spriter($folder, $dest = 'sprite.png', $name_css);
        } elseif (strpos($argv[0], "--padding=") === 0 || strpos($argv[0], "-p=") === 0) {
            $padding = $argv[0];
            $padding = strstr($padding, '=');
            $padding = substr($padding, 1);
            $padding = intval($padding);
            if (is_int($padding)) {
                $folder = $argv[1];
                $folder = iterative($folder);
                spriter($folder, $dest = 'sprite.png', $name_css = 'style.css', $padding);
            } else {
                echo "Error : Padding must be a number" . PHP_EOL;
            }
        } elseif (strpos($argv[0], "--override-size=") === 0 || strpos($argv[0], "-o=") === 0) {
            $size = $argv[0];
            $size = strstr($size, '=');
            $size = substr($size, 1);
            $size = intval($size);
            if (is_int($size)) {
                $size = $size . "x" . $size;
                shell_exec("cd img && mogrify -resize $size *.png");
                $folder = $argv[1];
                $folder = iterative($folder);
                spriter($folder);
            } else {
                echo "Error : Size must be type a number" . PHP_EOL;
            }
        } else {
            echo "Error : No valid argument" . PHP_EOL;
        }
    } else {
        echo "Error : Too much arguments" . PHP_EOL;
    }
} else {
    echo "Error : Can't find the argument" . PHP_EOL;
}
?>