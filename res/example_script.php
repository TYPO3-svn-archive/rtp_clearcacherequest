<?php
	
	//
	$url			= 'http://www.mysite.com/';
	$pluginPage		= 19;
	$clearPageCache	= '12,96';
	$secretKey		= 'OUX1p%B/aT7D,NMVJp%eaX;LTG:3x?PrbZ';
	
	//
	$clearCacheRequest = new HttpRequest($url, HttpRequest::METH_GET);
	$clearCacheRequest->addQueryData(array(
		'id' 				=> $pluginPage,
		'clearPageCache' 	=> $clearPageCache,
		'secretKey' 		=> sha1($secretKey)
	));
	
	//
	try {
	    $clearCacheRequest->send();
	    if ($clearCacheRequest->getResponseCode() == 200) {
	        echo 'Page cache cleared!';
	    } else {
	    	Throw new Exception('Clear cache request failed with response code: ' . $clearCacheRequest->getResponseCode()); 
	    }
	} catch (Exception $e) {
	    echo $e->getMessage();
	}