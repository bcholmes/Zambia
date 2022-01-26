import store from './store'

export const ADD_AUTH_CREDENTIAL = 'ADD_AUTH_CREDENTIAL';
export const LOGOUT = 'LOGOUT';
export const SHOW_LOGIN_MODAL = 'SHOW_LOGIN_MODAL';
export const HIDE_LOGIN_MODAL = 'HIDE_LOGIN_MODAL';

export function addAuthCredential(jwt) {
   let payload = {
      jwt: jwt
   }
   return {
      type: ADD_AUTH_CREDENTIAL,
      payload
   }
}
export function logout() {
    return {
       type: LOGOUT
    }
}

export function showLoginModal() {
   return {
      type: SHOW_LOGIN_MODAL
   }
}

export function hideLoginModal() {
   return {
      type: HIDE_LOGIN_MODAL
   }
}

export function extractJwt(res) {
   let authHeader = res.headers['authorization'];
   if (authHeader && authHeader.indexOf('Bearer ') === 0) {
       return authHeader.substring('Bearer '.length);
   } else {
       return undefined;
   }
}

export function extractAndDispatchJwt(res) {
   let jwt = extractJwt(res);
   if (jwt) {
      store.dispatch(addAuthCredential(jwt));
   } else {
      store.dispatch(logout());
   }
}

