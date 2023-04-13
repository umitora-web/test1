<?php
/* seraphinite-accelerator */
function seraph_accel_siteSettInlineDetach($siteId){ return array (
  'full' => true,
  'cache' => 
  array (
    'enable' => true,
    'normAgent' => true,
    'srv' => false,
    'srvClr' => false,
    'nginx' => 
    array (
      'fastCgiDir' => '',
      'fastCgiLevels' => '1:2',
    ),
    'cron' => true,
    'lazyInv' => true,
    'lazyInvForcedTmp' => false,
    'lazyInvTmp' => false,
    'updPost' => true,
    'updPostOp' => 0,
    'updPostDeps' => 
    array (
      0 => '@home',
      1 => '@post@{ID}:@pageNums',
      2 => '@post@{ID}:@commentPageNums',
      3 => '@postsBase@{post_type}:<|@pageNums|@commentPageNums>',
      4 => '@termsOfClass@categories@{post_type}@{ID}:<|@pageNums|@commentPageNums>',
    ),
    'updPostMeta' => false,
    'updPostMetaExcl' => 
    array (
      0 => '@^\\d+$@',
      1 => '@^_edit_lock$@',
      2 => '@^classic-editor-remember$@',
      3 => '@post_views_@',
      4 => '@^import_started_at@',
    ),
    'updTerms' => false,
    'updTermsOp' => 2,
    'updTermsDeps' => 
    array (
      0 => 'category',
      1 => 'product_cat',
      2 => 'course_cat',
    ),
    'updAllDeps' => 
    array (
      0 => '@home',
      1 => '@postsViewable:<|@pageNums|@commentPageNums>',
    ),
    'updSche' => 
    array (
      'def' => 
      array (
        'enable' => false,
        'op' => 0,
        'period' => 24,
        'periodN' => 1,
        'times' => 
        array (
          0 => 
          array (
            'm' => 0,
            'tm' => 0,
          ),
        ),
        'deps' => 
        array (
          0 => '@home',
        ),
      ),
    ),
    'maxProc' => 2,
    'procInterval' => 0,
    'procIntervalShort' => 0,
    'procMemLim' => 2048,
    'procTmLim' => 570,
    'procWorkInt' => 0.0,
    'procPauseInt' => 0.0,
    'autoProc' => true,
    'timeout' => 1440,
    'timeoutCln' => 262080,
    'ctxTimeoutCln' => 21600,
    'autoClnPeriod' => 1440,
    'useTimeoutClnForWpNonce' => true,
    'encs' => 
    array (
      0 => '',
      1 => 'gzip',
      2 => 'deflate',
      3 => 'compress',
    ),
    'dataCompr' => 
    array (
      0 => 'deflate',
    ),
    'chunks' => 
    array (
      'enable' => true,
      'js' => true,
      'css' => true,
      'seps' => 
      array (
        0 => 
        array (
          'enable' => true,
          'sel' => './/header[1]',
          'side' => 3,
        ),
        1 => 
        array (
          'enable' => true,
          'sel' => './/footer[last()]',
          'side' => 3,
        ),
        2 => 
        array (
          'enable' => true,
          'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " header ")][1]',
          'side' => 3,
        ),
        3 => 
        array (
          'enable' => true,
          'sel' => './/div[contains(concat(" ", normalize-space(@class), " "), " footer ")][last()]',
          'side' => 3,
        ),
        4 => 
        array (
          'enable' => true,
          'sel' => './/div[@id="comments" and contains(concat(" ", normalize-space(@class), " "), " comments-area ")]',
          'side' => 3,
        ),
        5 => 
        array (
          'enable' => true,
          'sel' => './/div[@data-elementor-type="header"]',
          'side' => 3,
        ),
        6 => 
        array (
          'enable' => true,
          'sel' => './/div[@data-elementor-type="footer"]',
          'side' => 3,
        ),
      ),
    ),
    'urisExcl' => 
    array (
    ),
    'exclAgents' => 
    array (
      0 => 'printfriendly',
    ),
    'exclCookies' => 
    array (
    ),
    'exclArgsAll' => true,
    'exclArgs' => 
    array (
      0 => 'aiosp_sitemap_path',
      1 => 'aiosp_sitemap_page',
      2 => 'xml_sitemap',
      3 => 'seopress_sitemap',
      4 => 'seopress_news',
      5 => 'seopress_video',
      6 => 'seopress_cpt',
      7 => 'seopress_paged',
      8 => 'sitemap',
      9 => 'sitemap_n',
    ),
    'skipArgsAll' => false,
    'skipArgs' => 
    array (
      0 => 'redirect_to',
      1 => 'utm_source',
      2 => 'utm_medium',
      3 => 'utm_campaign',
      4 => 'utm_content',
      5 => 'utm_term',
      6 => 'fbclid',
      7 => 'gclid',
      8 => '_ga',
      9 => 'yclid',
    ),
    'exclConts' => 
    array (
    ),
    'hdrs' => 
    array (
      0 => '@^Set-Cookie\\s*:\\s*wordpress_test_cookie\\s*=@i',
      1 => '@^X-XSS-Protection\\s*:@i',
      2 => '@^X-Frame-Options\\s*:@i',
      3 => '@^Content-Security-Policy\\s*:@i',
      4 => '@^Referrer-Policy\\s*:@i',
      5 => '@^Feature-Policy\\s*:@i',
      6 => '@^Permissions-Policy\\s*:@i',
    ),
    'views' => true,
    'viewsDeviceGrps' => 
    array (
      0 => 
      array (
        'enable' => false,
        'name' => '',
        'id' => 'mobilehighres',
        'agents' => 
        array (
          0 => 'android',
          1 => 'bada',
          2 => 'incognito',
          3 => 'kindle',
          4 => 'maemo',
          5 => 'mobi',
          6 => 'opera mini',
          7 => 's8000',
          8 => 'series60',
          9 => 'ucbrowser',
          10 => 'ucweb',
          11 => 'webmate',
          12 => 'webos',
        ),
      ),
      1 => 
      array (
        'enable' => false,
        'name' => '',
        'id' => 'mobilelowres',
        'agents' => 
        array (
          0 => '240x320',
          1 => '2.0 mmp',
          2 => '\\bppc\\b',
          3 => 'alcatel',
          4 => 'amoi',
          5 => 'asus',
          6 => 'au-mic',
          7 => 'audiovox',
          8 => 'avantgo',
          9 => 'benq',
          10 => 'bird',
          11 => 'blackberry',
          12 => 'blazer',
          13 => 'cdm',
          14 => 'cellphone',
          15 => 'danger',
          16 => 'ddipocket',
          17 => 'docomo',
          18 => 'dopod',
          19 => 'elaine/3.0',
          20 => 'ericsson',
          21 => 'eudoraweb',
          22 => 'fly',
          23 => 'haier',
          24 => 'hiptop',
          25 => 'hp.ipaq',
          26 => 'htc',
          27 => 'huawei',
          28 => 'i-mobile',
          29 => 'iemobile',
          30 => 'iemobile/7',
          31 => 'iemobile/9',
          32 => 'j-phone',
          33 => 'kddi',
          34 => 'konka',
          35 => 'kwc',
          36 => 'kyocera/wx310k',
          37 => 'lenovo',
          38 => 'lg',
          39 => 'lg/u990',
          40 => 'lge vx',
          41 => 'midp',
          42 => 'midp-2.0',
          43 => 'mmef20',
          44 => 'mmp',
          45 => 'mobilephone',
          46 => 'mot-v',
          47 => 'motorola',
          48 => 'msie 10.0',
          49 => 'netfront',
          50 => 'newgen',
          51 => 'newt',
          52 => 'nintendo ds',
          53 => 'nintendo wii',
          54 => 'nitro',
          55 => 'nokia',
          56 => 'novarra',
          57 => 'o2',
          58 => 'openweb',
          59 => 'opera mobi',
          60 => 'opera.mobi',
          61 => 'p160u',
          62 => 'palm',
          63 => 'panasonic',
          64 => 'pantech',
          65 => 'pdxgw',
          66 => 'pg',
          67 => 'philips',
          68 => 'phone',
          69 => 'playbook',
          70 => 'playstation portable',
          71 => 'portalmmm',
          72 => 'proxinet',
          73 => 'psp',
          74 => 'qtek',
          75 => 'sagem',
          76 => 'samsung',
          77 => 'sanyo',
          78 => 'sch',
          79 => 'sch-i800',
          80 => 'sec',
          81 => 'sendo',
          82 => 'sgh',
          83 => 'sharp',
          84 => 'sharp-tq-gx10',
          85 => 'small',
          86 => 'smartphone',
          87 => 'softbank',
          88 => 'sonyericsson',
          89 => 'sph',
          90 => 'symbian',
          91 => 'symbian os',
          92 => 'symbianos',
          93 => 'toshiba',
          94 => 'treo',
          95 => 'ts21i-10',
          96 => 'up.browser',
          97 => 'up.link',
          98 => 'uts',
          99 => 'vertu',
          100 => 'vodafone',
          101 => 'wap',
          102 => 'willcome',
          103 => 'windows ce',
          104 => 'windows.ce',
          105 => 'winwap',
          106 => 'xda',
          107 => 'xoom',
          108 => 'zte',
        ),
      ),
      2 => 
      array (
        'enable' => false,
        'name' => '',
        'id' => 'mobile',
        'agents' => 
        array (
          0 => 'mobile',
          1 => 'android',
          2 => 'silk/',
          3 => 'kindle',
          4 => 'blackberry',
          5 => 'opera mini',
          6 => 'opera mobi',
        ),
      ),
    ),
    'viewsGeo' => 
    array (
      'enable' => false,
    ),
    'viewsCompatGrps' => 
    array (
      0 => 
      array (
        'enable' => true,
        'id' => 'c',
        'agents' => 
        array (
          0 => '@\\Wmsie \\d+\\.\\d+\\W@',
          1 => '@\\Wtrident/\\d+\\.\\d+\\W@',
          2 => '@\\Wyandexmetrika/\\d+\\.\\d+\\W@',
          3 => '@\\Wgoogleadsenseinfeed\\W@',
          4 => '!@\\Wchrome/\\d+\\W@ & @(?:\\W|^)safari/([\\d\\.]+)(?:\\W|$)@ < 603.3.8',
          5 => '@\\sMac\\sOS\\sX\\s([\\d\\_]+)@i < 10.12.6',
        ),
      ),
      1 => 
      array (
        'enable' => true,
        'id' => 'cm',
        'agents' => 
        array (
          0 => '@\\Wbingbot/\\d+\\.\\d+\\W@',
        ),
      ),
    ),
    'viewsGrps' => 
    array (
      0 => 
      array (
        'enable' => true,
        'name' => 'AMP',
        'cookies' => 
        array (
        ),
        'args' => 
        array (
          0 => 'amp',
        ),
      ),
      1 => 
      array (
        'enable' => true,
        'name' => 'WPML',
        'cookies' => 
        array (
          0 => 'wp_wcml_currency',
          1 => 'wcml_client_currency',
        ),
        'args' => 
        array (
          0 => 'lang',
        ),
      ),
      2 => 
      array (
        'enable' => true,
        'name' => 'WPtouch',
        'cookies' => 
        array (
          0 => 'wptouch-pro-cache-state',
          1 => 'wptouch-pro-view',
        ),
        'args' => 
        array (
        ),
      ),
      3 => 
      array (
        'enable' => true,
        'name' => 'VillaTheme WooCommerce Multi Currency',
        'cookies' => 
        array (
          0 => 'wmc_current_currency',
        ),
      ),
      4 => 
      array (
        'enable' => true,
        'name' => 'GDPR Cookie Consent',
        'cookies' => 
        array (
          0 => 'viewed_cookie_policy',
          1 => 'cli_user_preference',
        ),
      ),
      5 => 
      array (
        'enable' => true,
        'name' => 'Pixelmate Cookie Banner',
        'cookies' => 
        array (
          0 => 'pixelmate',
        ),
      ),
    ),
    'ctx' => false,
    'ctxSessSep' => true,
    'ctxCliRefresh' => true,
    'ctxGrps' => 
    array (
      'common' => 
      array (
        'enable' => true,
        'name' => 'Common',
        'cookies' => 
        array (
          0 => 'wp-postpass_',
          1 => 'comment_author_',
          2 => 'sc_commented_posts',
        ),
        'args' => 
        array (
          0 => 'key',
        ),
        'tables' => 
        array (
          0 => 
          array (
            'name' => '%PREFIX%users',
            'col' => 'ID',
          ),
          1 => 
          array (
            'name' => '%PREFIX%usermeta',
            'col' => 'user_id',
          ),
        ),
      ),
      'wordpress-social-login' => 
      array (
        'enable' => true,
        'name' => 'Social Login',
        'args' => 
        array (
          0 => 'action',
        ),
      ),
      'theme_woodmart' => 
      array (
        'enable' => true,
        'name' => 'WoodMart Theme',
        'cookies' => 
        array (
          0 => 'woodmart_wishlist_products',
        ),
      ),
      'jet-cw' => 
      array (
        'enable' => true,
        'name' => 'Jet',
        'cookies' => 
        array (
          0 => 'jet-wish-list',
          1 => 'jet-compare-list',
        ),
      ),
      'woocommerce' => 
      array (
        'enable' => true,
        'name' => 'WooCommerce',
        'cookies' => 
        array (
          0 => 'woocommerce_cart_hash',
          1 => 'wp_woocommerce_session_',
          2 => 'yith_wcwl_session_',
        ),
        'args' => 
        array (
          0 => 'add-to-cart',
          1 => 'remove_item',
          2 => 'removed_item',
          3 => 'undo_item',
          4 => 'update_cart',
          5 => 'proceed',
          6 => 'order_again',
          7 => 'apply_coupon',
          8 => 'remove_coupon',
        ),
        'tables' => 
        array (
          0 => 
          array (
            'name' => '%PREFIX%woocommerce_sessions',
            'col' => 'session_key',
          ),
          1 => 
          array (
            'name' => '%PREFIX%posts',
            'col' => 'ID',
            'nameRel' => '%PREFIX%postmeta',
            'colRel' => 'meta_value',
            'colRelLink' => 'post_id',
            'condRel' => 
            array (
              'meta_key' => 
              array (
                0 => '_customer_user',
              ),
            ),
          ),
          2 => 
          array (
            'name' => '%PREFIX%postmeta',
            'col' => 'post_id',
            'nameRel' => '%PREFIX%postmeta',
            'colRel' => 'meta_value',
            'colRelLink' => 'post_id',
            'condRel' => 
            array (
              'meta_key' => 
              array (
                0 => '_customer_user',
              ),
            ),
          ),
        ),
      ),
      'lifterlms' => 
      array (
        'enable' => true,
        'name' => 'LMS by LifterLMS',
        'tables' => 
        array (
          0 => 
          array (
            'name' => '%PREFIX%lifterlms_sessions',
            'col' => 'session_key',
          ),
          1 => 
          array (
            'name' => '%PREFIX%posts',
            'col' => 'ID',
            'nameRel' => '%PREFIX%lifterlms_user_postmeta',
            'colRel' => 'user_id',
            'colRelLink' => 'post_id',
          ),
          2 => 
          array (
            'name' => '%PREFIX%postmeta',
            'col' => 'post_id',
            'nameRel' => '%PREFIX%lifterlms_user_postmeta',
            'colRel' => 'user_id',
            'colRelLink' => 'post_id',
          ),
        ),
      ),
      'wp-recall' => 
      array (
        'enable' => true,
        'name' => 'WP-Recall',
        'tables' => 
        array (
          0 => 
          array (
            'name' => '%PREFIX%rcl_bookmarks',
            'col' => 'user_id',
          ),
          1 => 
          array (
            'name' => '%PREFIX%rcl_bookmarks_gr',
            'col' => 'user_gr',
          ),
          2 => 
          array (
            'name' => '%PREFIX%rcl_chat_messages',
            'col' => 'user_id',
          ),
          3 => 
          array (
            'name' => '%PREFIX%rcl_chat_messagemeta',
            'col' => 'message_id',
            'nameRel' => '%PREFIX%rcl_chat_messages',
            'colRel' => 'user_id',
            'colRelLink' => 'message_id',
          ),
          4 => 
          array (
            'name' => '%PREFIX%rcl_chat_users',
            'col' => 'user_id',
          ),
          5 => 
          array (
            'name' => '%PREFIX%rcl_chats',
            'col' => 'chat_id',
            'nameRel' => '%PREFIX%rcl_chat_users',
            'colRel' => 'user_id',
            'colRelLink' => 'chat_id',
          ),
          6 => 
          array (
            'name' => '%PREFIX%rcl_feeds',
            'col' => 'user_id',
          ),
          7 => 
          array (
            'name' => '%PREFIX%rcl_groups_users',
            'col' => 'user_id',
          ),
          8 => 
          array (
            'name' => '%PREFIX%rcl_groups_options',
            'col' => 'group_id',
            'nameRel' => '%PREFIX%rcl_groups_users',
            'colRel' => 'user_id',
            'colRelLink' => 'group_id',
          ),
          9 => 
          array (
            'name' => '%PREFIX%rcl_groups',
            'col' => 'ID',
            'nameRel' => '%PREFIX%rcl_groups_users',
            'colRel' => 'user_id',
            'colRelLink' => 'group_id',
          ),
          10 => 
          array (
            'name' => '%PREFIX%rcl_notifications',
            'col' => 'user_id',
          ),
          11 => 
          array (
            'name' => '%PREFIX%rcl_orders',
            'col' => 'user_id',
          ),
          12 => 
          array (
            'name' => '%PREFIX%rcl_order_items',
            'col' => 'order_id',
            'nameRel' => '%PREFIX%rcl_orders',
            'colRel' => 'user_id',
            'colRelLink' => 'order_id',
          ),
          13 => 
          array (
            'name' => '%PREFIX%rcl_rating_values',
            'col' => 'user_id',
          ),
          14 => 
          array (
            'name' => '%PREFIX%rcl_rating_totals',
            'col' => 'object_id',
            'nameRel' => '%PREFIX%rcl_rating_values',
            'colRel' => 'user_id',
            'colRelLink' => 'object_id',
          ),
          15 => 
          array (
            'name' => '%PREFIX%rcl_rating_users',
            'col' => 'user_id',
          ),
          16 => 
          array (
            'name' => '%PREFIX%rcl_temp_media',
            'col' => 'user_id',
          ),
          17 => 
          array (
            'name' => '%PREFIX%rcl_user_action',
            'col' => 'user',
          ),
          18 => 
          array (
            'name' => '%PREFIX%rcl_uw_shares',
            'col' => 'author_id',
          ),
        ),
      ),
    ),
  ),
  'cacheBr' => 
  array (
    'enable' => true,
    'timeout' => 43200,
  ),
  'contPr' => 
  array (
    'enable' => true,
    'normalize' => 3,
    'normUrl' => false,
    'normUrlMode' => 2,
    'min' => true,
    'cln' => 
    array (
      'cmts' => true,
      'cmtsExcl' => 
      array (
        0 => '@^\\s*/?noindex\\s*$@i',
        1 => '@\\[et-ajax\\]@i',
      ),
      'items' => 
      array (
      ),
    ),
    'lazy' => 
    array (
      'items' => 
      array (
      ),
      'bjs' => true,
    ),
    'earlyPaint' => true,
    'img' => 
    array (
      'srcAddLm' => false,
      'sysFlt' => false,
      'inlSml' => true,
      'inlSmlSize' => 1024,
      'webp' => 
      array (
        'enable' => true,
        'redir' => true,
        'prms' => 
        array (
          'q' => 80,
        ),
      ),
      'avif' => 
      array (
        'enable' => false,
        'redir' => false,
        'prms' => 
        array (
          'q' => 52,
          's' => 6,
        ),
      ),
      'szAdaptBg' => false,
      'szAdaptExcl' => 
      array (
      ),
      'szAdaptBgCxMin' => 1366,
      'lazy' => 
      array (
        'setSize' => false,
        'load' => true,
        'smoothAppear' => true,
        'del3rd' => true,
        'excl' => 
        array (
          0 => './/img[contains(concat(\' \',normalize-space(@class),\' \'),\' jetpack-lazy-image \')]',
          1 => './/img[@data-opt-src]',
          2 => './/img[contains(concat(" ",normalize-space(@class)," ")," rev-slidebg ")]',
          3 => './/img[contains(concat(" ",normalize-space(@class)," ")," a3-notlazy ")]',
        ),
      ),
      'cacheExt' => 
      array (
        0 => '@\\.cdninstagram\\.com/@',
        1 => '@\\.googleusercontent\\.com/@',
      ),
    ),
    'frm' => 
    array (
      'lazy' => 
      array (
        'enable' => true,
        'yt' => true,
        'vm' => true,
        'elmntrBg' => true,
        'youTubeFeed' => true,
      ),
    ),
    'cp' => 
    array (
      'sldBdt' => true,
      'swBdt' => true,
      'elmntrBgSldshw' => true,
      'elmntrVids' => true,
      'prtThSkel' => true,
      'fltsmThBgFill' => true,
      'ukSldshw' => true,
      'sldN2Ss' => true,
      'sldRev' => true,
      'tdThumbCss' => true,
      'elmsKitImgCmp' => true,
      'haCrsl' => true,
      'elmntrTabs' => true,
      'elmntrAdvTabs' => true,
      'elmntrNavMenu' => true,
      'elmntrWdgtGal' => true,
      'phtncThmb' => true,
      'jetMobMenu' => true,
      'diviMvImg' => true,
      'diviMvText' => true,
      'diviVidBox' => true,
      'scrlSeq' => true,
      'fusionBgVid' => true,
    ),
    'js' => 
    array (
      'groupNonCrit' => false,
      'groupExclMdls' => true,
      'groupExcls' => 
      array (
        0 => 'src:@stripe@',
      ),
      'min' => false,
      'minExcls' => 
      array (
      ),
      'other' => 
      array (
        'incl' => 
        array (
          0 => 'descendant::iframe[@id=\'likes-master\' and contains(@src,\'//widgets.wp.com/likes/master.html?\')]',
        ),
      ),
      'cprRem' => false,
      'optLoad' => true,
      'cplxDelay' => false,
      'preLoadEarly' => false,
      'loadFast' => false,
      'clk' => 
      array (
        'delay' => 250,
        'excl' => 
        array (
        ),
        'exclDef' => 
        array (
          0 => './/*[starts-with(@href,"#elementor-action")]',
          1 => './/a[@e-action-hash]',
          2 => './/a[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_play ")]',
          3 => './/a[contains(concat(" ",normalize-space(@class)," ")," sby_video_thumbnail ")]',
          4 => './/a[contains(concat(" ",normalize-space(@class)," ")," jet-button__instance ")]',
        ),
      ),
      'nonCrit' => 
      array (
        'inl' => true,
        'int' => true,
        'ext' => true,
        'excl' => true,
        'items' => 
        array (
          0 => 'body:@\\Wfunction\\s+et_core_page_resource_fallback\\W@',
          1 => 'body:@\\WTRINITY_TTS_WP_CONFIG\\W@',
          2 => 'id:@^spai_js$@',
        ),
        'timeout' => 
        array (
          'enable' => true,
          'v' => 4500,
        ),
      ),
      'spec' => 
      array (
        'timeout' => 
        array (
          'enable' => false,
          'v' => 500,
        ),
        'items' => 
        array (
          0 => '@googletagmanager\\.com@i',
          1 => '@(?:^|\\W)gtag\\s*\\(\\s*[\'"]@',
          2 => '@google-analytics\\.com@i',
          3 => '@(?:^|\\W)ga\\s*\\(\\s*[\'"]create\\W@',
          4 => '@(?:^|\\W)ga\\s*\\(\\s*[\'"][^\'"]*\\.?send\\W@',
          5 => '@\\Wgoogleadservices\\.com\\W@i',
          6 => '@\\Wgooglesyndication\\.com/pagead/js/adsbygoogle\\.js(?:$|\\W)@',
          7 => '@(?:^|\\W)window\\s*.\\s*adsbygoogle(?:$|\\W)@',
          8 => '@\\Wgstatic\\.com\\W@',
          9 => '@//apis\\.google\\.com/js/plusone\\.js$@',
          10 => '@//apis\\.google\\.com/js/platform\\.js$@',
          11 => '!body:@elementor@i & src,body:@connect\\.facebook\\.net@i',
          12 => '@(?:^|\\W)fbq\\s*\\(\\s*[\'"]@',
          13 => '@static\\.hotjar\\.com@i',
          14 => 'src:@\\Wtrinitymedia\\.ai/player\\W@',
          15 => '@mc.yandex.ru/metrika@i',
          16 => '@(?:^|\\W)ym\\s*\\(\\s*\\d@',
          17 => '@\\Wyastatic\\.net\\W@i',
          18 => '@\\Wcdn\\.jsdelivr\\.net/npm/yandex-metrica-watch/[\\w\\-]+\\.js@',
          19 => '@\\Wsite\\.yandex\\.net/v[\\d\\.]+/js/all\\.js\\W@s',
          20 => '@\\Wyandex\\.st/share/share\\.js@',
          21 => '@\\Wapi-maps\\.yandex\\.ru/services/constructor\\W@i',
          22 => 'body:@\\Wymaps\\W@',
          23 => '@top-fwz1\\.mail\\.ru@i',
          24 => '@(?:^|\\W)_tmr\\s*\\.@',
          25 => '@\\Wconnect\\.ok\\.ru/connect\\.js\\W@',
          26 => '@\\Wlc2ads\\.ru/js/adv_out\\.js@',
          27 => '@counter\\.yadro\\.ru/hit\\?@i',
          28 => '@\\Wadvertur\\.ru\\W@',
          29 => '@\\Wapi\\.content-ad\\.net\\W@',
          30 => '@\\Wuptolike\\.com\\W@i',
          31 => '@\\Wshare\\.pluso\\.ru\\W@',
          32 => '@\\Wsendpulse\\.com/js/push/@',
          33 => '@\\Wmediametrics\\.ru/partner/inject/inject\\.js@',
          34 => '@code[\\w-]*\\.jivosite\\.com@i',
          35 => '@\\Wtawk\\.to\\W@',
          36 => '@\\Wgetbutton\\.io\\W@',
          37 => '@bigreal\\.org/@i',
          38 => '@\\.realbig\\.media/@i',
          39 => '@\\Wtruenat\\.bid\\W@i',
          40 => '@\\Wnewup\\.bid\\W@i',
          41 => '@\\Wnewrrb\\.bid\\W@i',
          42 => '@\\Wrb_ajaxurl\\W@',
          43 => '@\\Wcontent_rb\\W@',
          44 => '@gi\\[\'ads\'\\]\\[\'siteAdBlock\'\\]&&gi\\[\'ads\'\\]\\[\'blockAdBlock\'\\]&&gh\\[\'setAttribute\'\\]\\(\'data-ad-block\'@',
          45 => '@\\Wgismeteo\\.ru/api/informer/getinformer/@',
          46 => '@\\Wvk\\.com/js/api/openapi\\.js\\W@',
          47 => '@(?:^|\\W)VK\\s*\\.\\s*Widgets\\W@s',
          48 => '@(?:^|\\W)VK\\s*\\.\\s*init\\s*\\(@s',
          49 => '@\\.addtoany\\.com\\W@i',
          50 => '@\\Wtravelpayouts\\.com\\W@i',
          51 => 'src:@\\Wcalendly\\.com@',
          52 => '@/woocommerce-gateway-stripe/assets/js/stripe(?:\\.|\\.min\\.)js\\W@',
          53 => '@/amp\\-analytics\\-@i',
          54 => '@\\w*\\.write\\s*\\(\\s*[\'"]<a\\s*[^>]*href\\s*=\\s*[\'"][^\'"]*www\\.liveinternet\\.ru\\W@',
          55 => '@\\w*\\.write\\s*\\(\\s*[\'"]<img\\s*[^>]*src\\s*=\\s*[\'"][^\'"]*\\Wcounter\\.yadro\\.ru\\W@',
          56 => '@\\Wadvertur\\.ru/v\\d+/code\\.js\\?id=\\d+@',
          57 => '@\\.googlesyndication\\.com/pagead/show_ads\\.js$@',
          58 => '@(?:^|\\W)amzn_assoc_placement\\s*=\\s*[\\\'"][\\w\\-]+[\\\'"]@',
          59 => '@\\Wamazon-adsystem\\.com/widgets/onejs\\W@',
          60 => '@\\.\\s*write\\s*\\(.+<div.+div>.+\\Wuptolike\\.com\\W@s',
          61 => '@\\Wbeeketing\\.com\\W@',
          62 => '@counter\\.rambler\\.ru/top100\\.jcn\\?@',
          63 => 'body:@function\\(c,h,i,m,p\\)@',
        ),
      ),
      'skips' => 
      array (
      ),
    ),
    'css' => 
    array (
      'corrErr' => true,
      'group' => true,
      'groupCombine' => false,
      'groupNonCrit' => true,
      'groupNonCritCombine' => false,
      'groupFont' => true,
      'groupFontCombine' => true,
      'fontPreload' => false,
      'sepImp' => true,
      'min' => true,
      'optLoad' => true,
      'inlAsSrc' => false,
      'inlCrit' => true,
      'inlNonCrit' => false,
      'delayNonCritWithJs' => true,
      'nonCrit' => 
      array (
        'auto' => true,
        'autoExcls' => 
        array (
        ),
        'inl' => true,
        'int' => true,
        'ext' => true,
        'excl' => false,
        'items' => 
        array (
        ),
      ),
      'fontOptLoad' => true,
      'fontOptLoadDisp' => 'swap',
      'fontCrit' => false,
      'skips' => 
      array (
        0 => 'id:@^reycore-critical-css$@',
      ),
      'custom' => 
      array (
        0 => 
        array (
          'enable' => true,
          'data' => '',
        ),
        'jet-menu' => 
        array (
          'enable' => false,
          'descr' => 'Jet Menu',
          'data' => '.seraph-accel-js-lzl-ing ul.jet-menu > li[id^=jet-menu-item-] {
	display: none!important;
}',
        ),
        'jet-testimonials' => 
        array (
          'enable' => true,
          'descr' => 'Jet Testimonials',
          'data' => '.jet-testimonials__instance:not(.slick-initialized) .jet-testimonials__item {
	max-width: 100%;
}

.jet-testimonials__instance:not(.slick-initialized) .jet-testimonials__item:nth-child(n+4) {
	display: none !important;
}',
        ),
        'xo-slider' => 
        array (
          'enable' => true,
          'descr' => 'XO Slider',
          'data' => '.xo-slider .slide-content {
	display: unset!important;
}',
        ),
        'owl-carousel' => 
        array (
          'enable' => true,
          'descr' => 'OWL Carousel',
          'data' => '.owl-carousel:not(.owl-loaded) {
	display:block!important;
}

.owl-carousel:not(.owl-loaded) > *:not(:first-child) {
	display:none;
}',
        ),
        'ult-carousel' => 
        array (
          'enable' => true,
          'descr' => 'Ultimate Carousel',
          'data' => '.seraph-accel-js-lzl-ing .ult-carousel-wrapper {
	visibility:initial!important;
}

.seraph-accel-js-lzl-ing .ult-carousel-wrapper .ult-item-wrap:not(:first-child) {
	display:none;
}',
        ),
        'bdt-slideshow' => 
        array (
          'enable' => true,
          'descr' => 'Airtech Plumber Slider',
          'data' => '.seraph-accel-js-lzl-ing .bdt-prime-slider-previous,.seraph-accel-js-lzl-ing .bdt-prime-slider-next {
	display: none!important;
}',
        ),
        'n2-ss-slider' => 
        array (
          'enable' => true,
          'descr' => 'Smart Slider',
          'data' => 'ss3-force-full-width, ss3-fullpage {
	transform:none!important;
	opacity: 1!important;
	width: var(--seraph-accel-client-width)!important;
	margin-left: calc((100% - var(--seraph-accel-client-width)) / 2);
}

ss3-fullpage {
	height: 100vh!important;
}

.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-slide-backgrounds [data-public-id="1"],
.n2-ss-slider:not(.n2-ss-loaded) [data-slide-public-id="1"] {
	transform: translate3d(0px, 0px, 0px)!important;
}

.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-slide:not([data-slide-public-id="1"]),
.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-layer.js-lzl-n-ing,
.n2-ss-slider:not(.n2-ss-loaded):not([style*=ss-responsive-scale]) [data-responsiveposition],
.n2-ss-slider:not(.n2-ss-loaded):not([style*=ss-responsive-scale]) [data-responsivesize],
.n2-ss-slider.n2-ss-loaded .n2-ss-layer.js-lzl-ing {
	visibility: hidden!important;
}

.n2-ss-slider:not(.n2-ss-loaded) [data-slide-public-id="1"] .n2-ss-layers-container {
	opacity: 1!important;
}

.n2-ss-slider-pane
{
	opacity: 1!important;
	animation-name: none!important;
	--self-side-margin: auto!important;
	--slide-width: 100%!important;
}',
        ),
        'wp-block-ultimate-post-slider' => 
        array (
          'enable' => true,
          'descr' => 'Block Ultimate Post Slider',
          'data' => '[class*=wp-block-ultimate-post-post-slider] .ultp-block-items-wrap:not(.slick-initialized) > .ultp-block-item:not(:first-child)
{
	display: none!important;
}',
        ),
        'preloaders' => 
        array (
          'enable' => true,
          'descr' => 'Preloaders',
          'data' => '#preloader,#page_preloader,#loader-wrapper,#royal_preloader,#loftloader-wrapper,.rokka-loader,.page-preloader-cover,.apus-page-loading,.medizco-preloder,e-page-transition {
	display:none!important;
}

body.royal_preloader{
	visibility:hidden!important;
}',
        ),
        'elementor-vis' => 
        array (
          'enable' => true,
          'descr' => 'Elementor (visibility and animation)',
          'data' => '.elementor-invisible {
	visibility: visible!important;
}

.elementor-element[data-settings*="animation\\""] {
	animation-name: none!important;
}',
        ),
        'elementor' => 
        array (
          'enable' => true,
          'descr' => 'Elementor',
          'data' => '.vc_row[data-vc-full-width-init="false"] {
	position: relative;
	width: var(--seraph-accel-client-width);
	left: calc((100% - var(--seraph-accel-client-width)) / 2);
}

.elementor-top-section.elementor-section-stretched[data-settings*="section-stretched"] {
	width: var(--seraph-accel-client-width)!important;
	left: calc((100% - var(--seraph-accel-client-width)) / 2)!important;
}

.seraph-accel-js-lzl-ing .elementor-headline-dynamic-text.elementor-headline-text-active {
	opacity: 1;
}',
        ),
        'et' => 
        array (
          'enable' => true,
          'descr' => 'Divi',
          'data' => '.et_animated {
	opacity: 1 !important;
}

.et_pb_section_video_bg > video {
	height: 100%;
}

.et_pb_preload .et_pb_section_video_bg, .et_pb_preload > div {
	visibility: visible !important;
}

.et_pb_gallery_grid .et_pb_gallery_item {
	display: block !important;
}',
        ),
        'tag-div' => 
        array (
          'enable' => true,
          'descr' => 'tagDiv',
          'data' => 'body.td-animation-stack-type0 .td-animation-stack .entry-thumb,
body.td-animation-stack-type0 .post img:not(.woocommerce-product-gallery img):not(.rs-pzimg),
body.td-animation-stack-type0 .td-animation-stack .td-lazy-img,
.tdb_header_menu .tdb-menu-items-pulldown.tdb-menu-items-pulldown-inactive {
	opacity: 1!important;
}',
        ),
        'photonic-thumb' => 
        array (
          'enable' => true,
          'descr' => 'Photonic Photo Gallery',
          'data' => '.photonic-thumb,
.photonic-thumb a img {
	display: unset !important;
}

.photonic-loading {
	display: none !important;
}

.photonic-stream * {
	animation-name: none !important;
}',
        ),
        'avia-slideshow' => 
        array (
          'enable' => true,
          'descr' => 'Avia Slideshow',
          'data' => '.avia-slideshow.av-default-height-applied .avia-slideshow-inner > li:first-child {
	opacity: 1 !important;
	visibility: visible !important;
}
',
        ),
        'rev-slider' => 
        array (
          'enable' => true,
          'descr' => 'Revolution Slider',
          'data' => 'rs-module[data-lzl-layout="fullwidth"] {
	width: var(--seraph-accel-client-width);
	left: calc((100% - var(--seraph-accel-client-width)) / 2);
}

rs-module[data-lzl-layout="fullscreen"] {
	width: var(--seraph-accel-client-width);
	left: calc((100% - var(--seraph-accel-client-width)) / 2);
	height: 100vh !important;
}',
        ),
        'fusion-vis' => 
        array (
          'enable' => true,
          'descr' => 'Fusion (visibility and animation)',
          'data' => '.fusion-animated {
	visibility: visible;
}
',
        ),
        'fusion-menu' => 
        array (
          'enable' => true,
          'descr' => 'Fusion Menu',
          'data' => '.fusion-menu-element-wrapper.loading {
	opacity: 1;
}

@media (max-width: 1024px) {
	.fusion-menu-element-wrapper.loading .fusion-menu {
		display: none;
	}

	.fusion-menu-element-wrapper.loading button {
		display: block !important;
	}

	.fusion-menu-element-wrapper.loading {
		display: flex;
	}
}',
        ),
      ),
    ),
    'cdn' => 
    array (
      'items' => 
      array (
        0 => 
        array (
          'enable' => true,
          'addr' => '',
          'types' => 
          array (
            0 => 'js',
            1 => 'css',
            2 => 'less',
            3 => 'gif',
            4 => 'jpeg',
            5 => 'jpg',
            6 => 'bmp',
            7 => 'png',
            8 => 'svg',
            9 => 'webp',
            10 => 'avif',
            11 => 'eot',
            12 => 'aac',
            13 => 'mp3',
            14 => 'mp4',
            15 => 'ogg',
            16 => 'pdf',
            17 => 'docx',
            18 => 'otf',
            19 => 'ttf',
            20 => 'woff',
          ),
          'uris' => 
          array (
            0 => 'wp-content',
            1 => 'wp-includes',
          ),
          'urisExcl' => 
          array (
          ),
        ),
      ),
    ),
    'grps' => 
    array (
      'items' => 
      array (
        '@' => 
        array (
          'enable' => 2,
          'name' => 'Common',
          'urisIncl' => 
          array (
          ),
          'patterns' => 
          array (
          ),
          'views' => 
          array (
          ),
          'sklSrch' => true,
          'sklExcl' => 
          array (
            0 => './/br',
            1 => './/script',
            2 => './/style',
            3 => './/link',
            4 => './/head',
            5 => './/svg[@width="0"][@height="0"]',
          ),
          'sklCssSelExcl' => 
          array (
            0 => '@[\\.#][\\w\\-]*[\\-_]([\\da-f]+)[\\W_]@i',
            1 => '@\\.(?:product_cat|product_tag|category|tag|pa|woocommerce-product-attributes-item--attribute|comment-author)[\\-_]([\\w\\-]+)@i',
          ),
        ),
        'desktop' => 
        array (
          'enable' => 0,
          'name' => 'Desktop',
          'urisIncl' => 
          array (
          ),
          'patterns' => 
          array (
          ),
          'views' => 
          array (
            0 => 'cmn',
          ),
          'sklSrch' => false,
          'sklExcl' => 
          array (
          ),
          'sklCssSelExcl' => 
          array (
          ),
          'contPr' => 
          array (
            'enable' => true,
            'jsOvr' => true,
            'js' => 
            array (
              'optLoad' => true,
              'nonCrit' => 
              array (
                'timeout' => 
                array (
                  'enable' => true,
                  'v' => 4500,
                ),
              ),
              'spec' => 
              array (
                'timeout' => 
                array (
                  'enable' => false,
                  'v' => 4500,
                ),
              ),
            ),
            'cssOvr' => false,
            'css' => 
            array (
              'nonCrit' => 
              array (
                'auto' => false,
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  'test' => 
  array (
    'contDelay' => false,
    'contDelayTimeout' => 5000,
    'contExtra' => false,
    'contExtraSize' => 524288,
  ),
  'hdrTrace' => false,
  'debugInfo' => false,
  'debug' => false,
  'emojiIcons' => false,
  'log' => false,
  'logScope' => 
  array (
    'upd' => false,
    'request' => false,
  ),
  'asyncUseCron' => true,
  'v' => 67,
); }
$seraph_accel_sites = array (
  'test' => 'm',
);
@include(WP_CONTENT_DIR . '/plugins/seraphinite-accelerator-ext/cache.php');
?>