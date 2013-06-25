<?php
include_once 'includes/database.php';

class Image
	{
		function do_resize_image($file, $width = 0, $height = 0, $proportional = true, $output = 'file')	{
			if($height <= 0 && $width <= 0)	return false;
			
			$info = getimagesize($file);
			$image = '';
		
			$final_width = 0;
			$final_height = 0;
			list($width_old, $height_old) = $info;
		
			if($proportional)	{
				if ($width == 0) $factor = $height/$height_old;
				elseif ($height == 0) $factor = $width/$width_old;
				else $factor = min ( $width / $width_old, $height / $height_old);
			
				$final_width = round ($width_old * $factor);
				$final_height = round ($height_old * $factor);
			
				if($final_width > $width_old && $final_height > $height_old)	{
					$final_width = $width_old;
					$final_height = $height_old;
				}
			}
			else	{
				$final_width = ( $width <= 0 ) ? $width_old : $width;
				$final_height = ( $height <= 0 ) ? $height_old : $height;
			}
		
			switch($info[2])	{
				case IMAGETYPE_GIF:
					$image = imagecreatefromgif($file);
				break;
				case IMAGETYPE_JPEG:
					$image = imagecreatefromjpeg($file);
				break;
				case IMAGETYPE_PNG:
					$image = imagecreatefrompng($file);
				break;
				default:
				
				return false;
			}
		
			$image_resized = imagecreatetruecolor( $final_width, $final_height );
		
			if(($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG))	{
				$trnprt_indx = imagecolortransparent($image);
		
				if($trnprt_indx >= 0)	{
						$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
						$trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
						imagefill($image_resized, 0, 0, $trnprt_indx);
						imagecolortransparent($image_resized, $trnprt_indx);
				}
				elseif($info[2] == IMAGETYPE_PNG)	{
						imagealphablending($image_resized, false);
						$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
						imagefill($image_resized, 0, 0, $color);
						imagesavealpha($image_resized, true);
				}
			}
			
			imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
		
			switch( strtolower($output))	{
				case 'browser':
					$mime = image_type_to_mime_type($info[2]);
					header("Content-type: $mime");
					$output = NULL;
				break;
				case 'file':
					$output = $file;
				break;
				case 'return':
					return $image_resized;
				break;
				default:
				break;
			}
		
			if(file_exists($output))	{
				@unlink($output);
			}
		
			switch($info[2])	{
				case IMAGETYPE_GIF:
					imagegif($image_resized, $output);
				break;
				case IMAGETYPE_JPEG:
					imagejpeg($image_resized, $output, 100);
				break;
				case IMAGETYPE_PNG:
					imagepng($image_resized, $output);
				break;
				default:
				return false;
			}
			return true;
		}

    	function do_resize_image2($file, $width = 0, $height = 0, $proportional = true, $output = 'file', $temppic)	{
			if($height <= 0 && $width <= 0)	{
			  return false;
			}
		
			$info = getimagesize($file);
			$image = '';
		
			$final_width = 0;
			$final_height = 0;
			list($width_old, $height_old) = $info;
		
			if($proportional)	{
				  if ($width == 0) $factor = $height/$height_old;
				  elseif ($height == 0) $factor = $width/$width_old;
				  else $factor = min ( $width / $width_old, $height / $height_old);
			
				  $final_width = round ($width_old * $factor);
				  $final_height = round ($height_old * $factor);
			
					if($final_width > $width_old && $final_height > $height_old)	{
						$final_width = $width_old;
						$final_height = $height_old;
					}
			}
			else	{
				  $final_width = ( $width <= 0 ) ? $width_old : $width;
				  $final_height = ( $height <= 0 ) ? $height_old : $height;
			}
		
			$owh = $width_old."x".$height_old;
			$nwh = $final_width."x".$final_height;
			if(!file_exists($temppic))
				{
					$runinbg = "convert ".$file." -coalesce ".$temppic;
					$runconvert = exec("$runinbg");
				}
			$runinbg = "convert -size ".$owh." ".$temppic." -resize ".$nwh." ".$output;
			$runconvert = exec("$runinbg");
			return true;
		}
	
		function generatevideothumbs($theconvertimg,$thevideoimgnew,$thewidth,$theheight)	{
			global $config;
			$convertimg = $theconvertimg;
			$videoimgnew = $thevideoimgnew;
		
			$theimagesizedata = GetImageSize($convertimg);
			$videoimgwidth = $theimagesizedata[0];
			$videoimgheight = $theimagesizedata[1];
			$videoimgformat = $theimagesizedata[2];
			
			$dest_width = $thewidth;
			$dest_height = $theheight;
			
			if($videoimgformat == 2)	{
				$videoimgsource = @imagecreatefromjpeg($convertimg);
				$videoimgdest = @imageCreateTrueColor($dest_width, $dest_height);
				ImageCopyResampled($videoimgdest, $videoimgsource, 0, 0, 0, 0, $dest_width, $dest_height, $videoimgwidth, $videoimgheight);
				imagejpeg($videoimgdest, $videoimgnew, 100);
				imagedestroy($videoimgsource);
				imagedestroy($videoimgdest);
			}
			elseif ($videoimgformat == 3)	{
				$videoimgsource = imagecreatefrompng($convertimg);
				$videoimgdest = imageCreateTrueColor($dest_width, $dest_height);
				ImageCopyResampled($videoimgdest, $videoimgsource, 0, 0, 0, 0, $dest_width, $dest_height, $videoimgwidth, $videoimgheight);
				imagepng($videoimgdest, $videoimgnew, 100);
				imagedestroy($videoimgsource);
				imagedestroy($videoimgdest);
			}
			else	{
				$videoimgsource = imagecreatefromgif($convertimg);
				$videoimgdest = imageCreateTrueColor($dest_width, $dest_height);
				ImageCopyResampled($videoimgdest, $videoimgsource, 0, 0, 0, 0, $dest_width, $dest_height, $videoimgwidth, $videoimgheight);
				imagejpeg($videoimgdest, $videoimgnew, 100);
				imagedestroy($videoimgsource);
				imagedestroy($videoimgdest);
			}
		}
		
		function delete_pic_images($thepp)	{
			global $config, $conn;
			
			if($thepp != "")	{		
				  $dp1 = $config['pdir']."/t/l-".$thepp;
				  @chmod($dp1, 0777);
				  if (file_exists($dp1))	
					  @unlink($dp1);
				  
				  $dp1 = $config['pdir']."/t/".$thepp;
				  @chmod($dp1, 0777);
				  if (file_exists($dp1))
					  @unlink($dp1);
				  
				  $dp1 = $config['pdir']."/t/s-".$thepp;
				  @chmod($dp1, 0777);
				  if (file_exists($dp1))
					  @unlink($dp1);
				  
				  $dp1 = $config['pdir']."/t/t-".$thepp;
				  @chmod($dp1, 0777);
				  if (file_exists($dp1))
					  @unlink($dp1);
			}
		}
    }
?>