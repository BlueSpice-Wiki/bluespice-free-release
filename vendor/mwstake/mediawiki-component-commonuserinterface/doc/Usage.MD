# LessVars

Get the LessVars singleton:

    $singleton = MWStake\MediaWiki\Component\CommonUserInterface\LessVars::getInstance();

Use this to override values

    $singleton->setVar( 'myVar', 'green' );

Retrieve var by `$singleton->getVar( $name )` or all by `$singleton->getAllVars()`

In order to use those vars in your RL modules, set RL module class to

    MWStake\MediaWiki\Component\CommonUserInterface\ResourceLoader\LessVars


