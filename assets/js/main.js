import 'bootstrap/js/src/alert';
import 'bootstrap/js/src/collapse';
import Tooltip from 'bootstrap/js/src/tooltip';
import './search';

window.process = {env: {}};
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
[...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl));

import 'bootstrap/dist/css/bootstrap.css';
import 'flag-icons/css/flag-icons.css';
import '../css/styles.css';
