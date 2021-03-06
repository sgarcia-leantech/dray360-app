import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import PoweredByD360Icon from '../components/Icons/PoweredByD360'
import EnvaseIcon from '../components/Icons/EnvaseLogo'

Vue.use(Vuetify)

/*
  Reference assets/styles/variables.scss
*/
export default new Vuetify({
  theme: {
    themes: {
      light: {
        // primary: '#326295',
        // secondary: '#5F7F00',
        primary: '#003C71',
        secondary: '#61788A',
        warning: '#cc904c',
        error: '#FF5252',
        black: '#303435',
        'slate-gray': '#61788A',
        'blue-light': '#41B6E6',
        'orange-changes': '#C25703',
      }
    },
    options: {
      customProperties: true
    }
  },
  icons: {
    values: {
      poweredBy360: {
        component: PoweredByD360Icon,
      },
      envase: {
        component: EnvaseIcon,
      },
    },
  },
})
