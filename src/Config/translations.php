<?php

return [

	// If you want to change the way you need to name your translation models.
	// Just do that here, by setting your version of the last part of the translation model.
	// Your free to use what ever you want.
	// like: "model_name" => "Translations",
	// or: "model_name" => "Trans",
	"model_name" => null,

	// This is the default location of your translation models.
	// If your models are in a different location please fill in the correct namespace.
	// default: "model_namespace" => "\App",
	"model_namespace" => null,

	// By default this package use the word `trans` to get all the translations from the request object.
	// This will look something like this: `trans[en][title]` to save the title for that language (locale)
	// If you do not like this way you can override the `trans` part by your given string.
	"form_elements" => null,

];