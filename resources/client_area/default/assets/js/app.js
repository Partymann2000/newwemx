import './bootstrap';
import 'flowbite';

import toastr from "toastr";
import "toastr/build/toastr.min.css";

// make it global so inline scripts/blade can access it
window.toastr = toastr;

// default options
toastr.options = {
    closeButton: true,
    progressBar: true,
    newestOnTop: true,
    preventDuplicates: false,
    timeOut: 4000,
    extendedTimeOut: 2000,
    positionClass: 'toast-bottom-right'
};

// listen for Livewire events
window.addEventListener('toast', (e) => {
    const { type = 'info', message = '', title = '' } = e.detail || {};
    toastr[type](message, title);
});
