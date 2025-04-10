import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

import tinymce from 'tinymce/tinymce';
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

// plugins de mise en forme
import 'tinymce/plugins/link';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/image';
import 'tinymce/plugins/code';
import 'tinymce/plugins/table';

// skin CSS
import 'tinymce/skins/ui/oxide/skin.min.css';

// initialisation
console.log('TinyMCE loading...');
tinymce.init({
    selector: 'textarea.tinymce',
    plugins: 'link lists image code table',
    toolbar: 'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link image | code',
    menubar: 'edit insert format tools table',
    branding: false,
    height: 500,
    license_key: 'gpl',
    promotion: false,
    });
console.log('TinyMCE loaded...🎉' );

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

