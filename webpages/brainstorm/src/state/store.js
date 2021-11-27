import { createStore, combineReducers } from 'redux'
import { ADD_AUTH_CREDENTIAL, LOGOUT } from './authActions';
import { SAVE_OPTIONS } from './optionsActions';

const authInitialState = {
    pending: true,
    jwt: undefined
}

const auth = (state = authInitialState, action) => {
    switch (action.type) {
        case ADD_AUTH_CREDENTIAL: 
            return {
                pending: false,
                jwt: action.payload.jwt
            }
        case LOGOUT: 
            return {
                pending: false,
                jwt: undefined
            };
        default:
            return state;
    }
};

const options = (state = {}, action) => {
    switch (action.type) {
        case SAVE_OPTIONS: 
            return { ...state,
                divisions: action.payload.divisions 
            };
        default:
            return state;
    }
};

const reducer = combineReducers({
    auth, options
})
const store = createStore(reducer);

export default store;