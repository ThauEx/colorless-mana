import 'bootstrap/js/src/alert';
import 'bootstrap/js/src/collapse';
import Tooltip from 'bootstrap/js/src/tooltip';
import './search-filter';

window.process = {env: {}};
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl));

import '@symfony/ux-live-component/styles/live.css';
import 'bootstrap-dark-5/dist/css/bootstrap-dark.css';
import 'flag-icons/css/flag-icons.css';
import '../css/styles.css';
