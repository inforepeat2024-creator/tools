<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

use RepeatToolkit\Helpers\StaticHelpers\Traits\ThemeTrait;

class SvgHelper
{

    public static function getSvgIcon($path, $iconClass = "", $svgClass = "") {
        $full_path = public_path('themes/'. ThemeTrait::getActiveThemeStatic() .'/media/' . $path);

        $path = $full_path;


        //dd($path);

        if ( ! file_exists($path)) {
            return "<!-- SVG file not found: ".$path." -->";
        }



        $svg_content = file_get_contents($path);

        $dom = new \DOMDocument();
        $dom->loadXML($svg_content);

        // remove unwanted comments
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query("//comment()") as $comment) {
            $comment->parentNode->removeChild($comment);
        }

        // add class to svg
        if ( ! empty($svgClass)) {
            foreach ($dom->getElementsByTagName("svg") as $element) {
                $element->setAttribute("class", $svgClass);
            }
        }

        // remove unwanted tags
        $title = $dom->getElementsByTagName("title");
        if ($title["length"]) {
            $dom->documentElement->removeChild($title[0]);
        }

        $desc = $dom->getElementsByTagName("desc");
        if ($desc["length"]) {
            $dom->documentElement->removeChild($desc[0]);
        }

        $defs = $dom->getElementsByTagName("defs");
        if ($defs["length"]) {
            $dom->documentElement->removeChild($defs[0]);
        }

        // remove unwanted id attribute in g tag
        $g =  $dom->getElementsByTagName("g");
        foreach ($g as $el) {
            $el->removeAttribute("id");
        }

        $mask =  $dom->getElementsByTagName("mask");
        foreach ($mask as $el) {
            $el->removeAttribute("id");
        }

        $rect =  $dom->getElementsByTagName("rect");
        foreach ($rect as $el) {
            $el->removeAttribute("id");
        }

        $path =  $dom->getElementsByTagName("path");
        foreach ($path as $el) {
            $el->removeAttribute("id");
        }

        $circle =  $dom->getElementsByTagName("circle");
        foreach ($circle as $el) {
            $el->removeAttribute("id");
        }

        $use =  $dom->getElementsByTagName("use");
        foreach ($use as $el) {
            $el->removeAttribute("id");
        }

        $polygon =  $dom->getElementsByTagName("polygon");
        foreach ($polygon as $el) {
            $el->removeAttribute("id");
        }

        $ellipse =  $dom->getElementsByTagName("ellipse");
        foreach ($ellipse as $el) {
            $el->removeAttribute("id");
        }

        $string = $dom->saveXML($dom->documentElement);

        // remove empty lines
        $string = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);

        $cls = array("svg-icon");

        if ( ! empty($iconClass)) {
            $cls = array_merge($cls, explode(" ", $iconClass));
        }

        return '<span class="custom_icon  '. implode(" ", $cls) . '">' . $string . "</span>";
    }

}
