import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

/* for Test */
window.dd = console.log;
