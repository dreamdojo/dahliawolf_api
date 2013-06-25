<?php
include_once 'includes/database.php';

#Load Magento Libraries for SSO.
//require_once ("/var/www/www.dahliawolf.com/htdocs/dahliawolf/shop/app/Mage.php");
//Mage::app("default");
//Mage::getSingleton('core/session', array('name'=>'frontend'));

class Magento
	{
    	/* Magento Authentication function .*/

		//createmagentouser
		function createUser($theemail, $thepassword, $thefirstname, $thelastname)	{
			require_once ("shop/app/Mage.php");
			umask(0);
			$websiteId 	= Mage::app()->getWebsite()->getId();
			$store 		= Mage::app()->getStore();
			$customer 	= Mage::getModel("customer/customer");
			$customer->website_id 		= $websiteId;
			$customer->setStore($store);
			$customer->firstname 		= $thefirstname;
			$customer->lastname 		= $thelastname;
			$customer->email 			= $theemail;
			$customer->password_hash 	= md5($thepassword);
			$customer->save();
		}
		
		//doSSOLoginCustomer
		function login($username, $validate)	{
			$websiteId = Mage::app()->getWebsite()->getId();
			$store = Mage::app()->getStore();
			$customer = Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setStore($store);
			$customer->loadByEmail($username);
			$session = Mage::getSingleton('customer/session');
			$core_session = Mage::getSingleton('core/session');
			$session->setCustomerAsLoggedIn($customer); 
			if($customer->getId())	{
				if($validate!=="1")	{
					Mage::dispatchEvent('pinscript_customer_login', array(
							'customer' => $customer
						));
					$coreSessionMessages = $core_session->getMessages(true);
					$messages_array = array();
					foreach($coreSessionMessages->getItems() as $msg)	{
						$messages_array[] = $msg->getText();
					}
					//Here missing transfer the message list ($messages_array) to popup window.
					pointsEarnPopup(1);
				}
				return true;
			}	else	{
				return false;
			}
		}
		
		//AddPointsPerComment
		function addPointsPerComment($username)	{
			$websiteId = Mage::app()->getWebsite()->getId();
			$store = Mage::app()->getStore();
			$customer = Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setStore($store);
			$customer->loadByEmail($username);
			
			if($customer->getId()){
				Mage::dispatchEvent('pinscript_customer_post_comment', array(
						'customer' => $customer));
				pointsEarnPopup(1);
				return true;
			}else{
				return false;
			}
		}
		
		//AddPointsPerImageUpload
		function addPointsPerImageUpload($username)	{
			$websiteId 	= Mage::app()->getWebsite()->getId();
			$store 		= Mage::app()->getStore();
			$customer 	= Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setStore($store);
			$customer->loadByEmail($username);
			
			if($customer->getId())	{
				pointsEarnPopup(20);
				Mage::dispatchEvent('pinscript_customer_post_image', array(
						'customer' => $customer));
			   
				return true;
			} else	{
				return false;
			}
		}
		
		//AddPointsPerImageShare
		function addPointsPerImageShare($username)	{
			$websiteId 	= Mage::app()->getWebsite()->getId();
			$store 		= Mage::app()->getStore();
			$customer 	= Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setStore($store);
			$customer->loadByEmail($username);
			
			if($customer->getId())	{
				pointsEarnPopup(10);
				Mage::dispatchEvent('pinscript_customer_share_facebook', array(
						'customer' => $customer));
				return true;
			} else	{
				return false;
			}
		}
		
		//SuperWolfBadge
		function showWolfBadge($username)	{
			$websiteId = Mage::app()->getWebsite()->getId();
			$store = Mage::app()->getStore();
			$customer = Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setStore($store);
			$customer->loadByEmail($username);
			
			$superWolfStatus = $customer->getSuperWolf();
			if($superWolfStatus=="0" || $superWolfStatus==NULL || $superWolfStatus==0)	{
				return false;
			} else if($superWolfStatus==1 || $superWolfStatus=="1")	{
				return true;
			} else	{
				return false;
			}
		}
		
		//AddOneTimePoints
		function addOneTimePoints($username, $custom_action_code, $points) {
			$websiteId = Mage::app()->getWebsite()->getId();
			$store = Mage::app()->getStore();
			$customer = Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setStore($store);
			$customer = $customer->loadByEmail($username);
			$customer_id=$customer->getId(); 
			Mage::helper('rewards/transfer')->createTransferForPinscriptCustomTransfer($customer_id,$custom_action_code,$points);
			
		}
		
		//GetUserAchievementLevel
		function getUserAchievementLevel($username)	{
			$websiteId 	= Mage::app()->getWebsite()->getId();
			$store 		= Mage::app()->getStore();
			$customer 	= Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setStore($store);
			$customer = $customer->loadByEmail($username);
			$customer_id = $customer->getId();
			$level=Mage::helper ('rewards/customer_points_index')->getCustomerLifetimePoints($customer_id);
			return $level;
		}
		
		//GetUserPoints
		function getUserPoints($username)	{
			$websiteId = Mage::app()->getWebsite()->getId();
			$store = Mage::app()->getStore();
			$customer = Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setStore($store);
			$customer = $customer->loadByEmail($username); //use session
			$customer_id=$customer->getId();
			$level = Mage::helper ('rewards/customer_points_index')->getCustomerLifetimePoints($customer_id);
			return $level;
		}
		
		
		//getRank
		function getRank($blop) {
			 $topusers = Mage::helper ( 'rewards/customer_points_index' )->getTopCustomerLifetimePoints($x);
		   
			for($i=0; $i < sizeof($topusers);$i++)	{
				if($topusers[$i]["customer_email"] == $blop)	{
					break;
				}
			}
			$i++;
			return $i;
		}
		
		//getTopUsers
		function getTopUsers($howmany) {
			$topusers = Mage::helper ( 'rewards/customer_points_index' )->getTopCustomerLifetimePoints($x);
		   
			for($i=0;$i<$howmany;$i++)	{
				if($topusers[$i]["customer_email"]<>"")	{
					$username = findUsernameByEmail($topusers[$i]["customer_email"]);
					$listtopusers .= "<div class='wolfpack-user-block' style='margin-left:".($i%3*45)."px'><div class='wolfpack-box-".($i % 2 ? 'even' : 'odd')."'><div class='prof-frame".($topusers[$i]["customer_email"] == $_SESSION['EMAIL'] ? '-user' : '')."'><a href='".$config["baseurl"]."/".$username."'><img class='wolfpack-user-image' src='".findProfilePicByEmail($topusers[$i]["customer_email"])."'></a></div><div class='wolfpack-user-data'><div class='wolfpack-user-name'><a href='".$config["baseurl"]."/".$username."'>".$username."</a></div>";
					$listtopusers .= "<div class='wolfpack-user-points'>POINTS ".$topusers[$i]["customer_points"]."</div><div class='wolfpack-user-rank'><p class='user-rank'>RANK</p>".($i+1)."</div></div></div></div>";
				}
			}
			return $listtopusers;
		}
		
		//pointsEarnPopup
		function pointsEarnPopup($howmany)	{
			//$_SESSION['POINTSEARNED'] = intval($howmany);
			sendEmailofPoints($howmany);
			return $howmany;
		}
		
		//showPointsPopup
		function showPointsPopup($howmany)	{
			if($howmany > 0)	{
				/*echo '<script>userPoints('.howmany.');</script>';*/
				//$_SESSION['POINTSEARNED'] = intval(0);
				
				return $howmany;
			}
		}
		
		//sendEmailofPoints
		function sendEmailofPoints($theemail, $howmany)	{
			$sendto 		= $theemail;
			$sendername 	= $config['site_name'];
			$from 			= $config['site_email'];
			$subject 		= "Points earned ".$sendername;
			$sendmailbody 	= "You have earned ".$howmany." points.<br><br>";
			
			//mailme($sendto,$sendername,$from,$subject,$sendmailbody,$bcc="");
		}
    }
?>