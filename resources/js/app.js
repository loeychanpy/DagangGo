console.log('APP JS LOADED');
import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import './transaction';
import './inventory';