kirby-twitter-plugin
====================

A Twitter plugin for the Kirby CMS, uses caching to load status' in case you're rate limited or Twitter is unreachable.


Use
===

    # Will get the last 10 statuses from my account :}
    Kirby_Twitter::Instance('dmackintosh88')->fetch(10);
    
    # Will get the last tweet my me
    Kirby_Twitter::Instance('dmackintosh88')->get(1);
    
    // // Read the doc blocks for documentation // //