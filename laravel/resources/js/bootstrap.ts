import axios from 'axios';

/*
 * Axios is configured with the CSRF cookie and credentials so Inertia's
 * session-based auth works. Laravel's `web` group already verifies the
 * XSRF-TOKEN cookie automatically.
 */
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

export default axios;