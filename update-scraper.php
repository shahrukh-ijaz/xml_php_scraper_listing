<?php

## Featured Listing Scrapper

$xml = new SimpleXMLElement('<listings/>');
header('Content-type: text/xml');

$html = file_get_contents('https://teamrita.com/idx/listings/featured-properties/');

$document = new DOMDocument();
libxml_use_internal_errors(true);
@$document->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($document);

$listings = $xpath->query("//ol[@id='dsidx-listings']/li/@onclick");
$images = $xpath->query("//div[@class='dsidx-photo']/a/img/@src");
$no_of_images = $xpath->query("//*[@class='dsidx-toolbar-content']/text()");

for($i=0; $i< count($listings); $i++){

    $listing_xml = $xml->addChild('listing');    

    $listing = $listings[$i];
    $no_of_image = $no_of_images[$i]->wholeText;

    $listing_url = $listing->value;
    $listing_url = "https://teamrita.com" . substr($listing_url, 15, -1);
    $image = $images[$i]->value;    

    $html2 = new DOMDocument();
    $html_source = file_get_contents($listing_url);

    libxml_use_internal_errors(true);
    @$html2->loadHtml($html_source);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($html2);

    $price = $xpath->query("//td[@data-dsidx='Price']/text()[1]");


    $description = $xpath->query("//*[@id='dsidx-description-text']/text()");
    $description = $description[0]->wholeText; 
    
    $description = htmlspecialchars($description, ENT_XML1, 'UTF-8');    
    
    $num_beds = $xpath->query("//*[contains(@data-dsidx,'Beds')]/text()");    
    $num_baths = $xpath->query("//*[contains(@data-dsidx,'Baths')]/text()");    
    $year_built = $xpath->query("//span[@data-dsidx='YearBuilt']/text()");    
    
    $image_src = substr($image, 0, -4);    
     
    for($image_id=1; $image_id<$no_of_image; $image_id++)
    {   
        $image_xml = $listing_xml->addChild('image'); 
        $value = $image_id;
        if ($image_id < 10) {
            $value = str_pad($image_id, 2, "0", STR_PAD_LEFT);
        }
        $src = $image_src . "_" . $value . ".jpg";                   
        $image_xml->addChild('url', $src);     
    }
    $image_xml = $listing_xml->addChild('image');
    $image_xml->addChild('url', $image); // add main header image

    $longitude = array();  
    $latitude = array();  
    $city = array();
    $region = array();
    $country = array();
    $postal_code = array();
    $home_listing_id = array();
    
    $html_source = file_get_contents($listing_url);

    libxml_use_internal_errors(true);
    @$html2->loadHtml($html_source);
    libxml_clear_errors();
    
    preg_match('/longitude = (.*);/', $html_source, $longitude);
   
    preg_match('/latitude = (.*);/', $html_source, $latitude);

    preg_match('/city = (.*);/', $html_source, $city);

    preg_match('/state = (.*);/', $html_source, $region);

    preg_match('/zip = (.*);/', $html_source, $postal_code);
    
    preg_match('/mls-(.*)-/', $listing_url, $home_listing_id);

    $title = $xpath->query("//*[@class='title-head']/text()");

    $title = $title[0]->wholeText; 
        
    $addr1 = explode(",",$title);

    $address = $listing_xml->addChild('address');  
        
    $component1 = $address->addChild('component', $addr1[0]);
    $component1->addAttribute('name', 'addr1');
    
    $component2 = $address->addChild('component', str_replace('"',"",$city[1]));
    $component2->addAttribute('name', 'city');

    $component3 = $address->addChild('component', str_replace('"',"",$region[1]));
    $component3->addAttribute('name', 'region');

    $component4 = $address->addChild('component', "United States");
    $component4->addAttribute('name', 'country');
    
    $component5 = $address->addChild('component', str_replace('"',"",$postal_code[1]));
    $component5->addAttribute('name', 'postal_code');
    
    $listing_xml->addChild('description',  $description);


    $newprice = str_replace("$", "", $price[0]->wholeText);


    $listing_xml->addChild('name',  $title);    
    $listing_xml->addChild('price',  $newprice);
    $listing_xml->addChild('num_beds',  $num_beds[0]->wholeText);
    $listing_xml->addChild('num_baths',  $num_baths[0]->wholeText);
    $listing_xml->addChild('listing_type', 'for_sale_by_agent');
    $listing_xml->addChild('url',  $listing_url);
    $listing_xml->addChild('year_built',  $year_built[0]->wholeText);
    $listing_xml->addChild('longitude',  $longitude[1]);
    $listing_xml->addChild('latitude',  $latitude[1]);
    $listing_xml->addChild('property_type', "house");
    $listing_xml->addChild('availability', "for_sale");
    $listing_xml->addChild('home_listing_id', $home_listing_id[1]); 

}
print($xml->asXML());
$xml->asXML("listings.xml");
?>