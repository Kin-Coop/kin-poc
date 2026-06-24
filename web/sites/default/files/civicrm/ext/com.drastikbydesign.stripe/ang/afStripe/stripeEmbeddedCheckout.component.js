(function (angular, $, _) {
  angular.module('afStripe').component('afStripeEmbeddedCheckout', {
    require: {
      afCheckoutBlock: '^^afCheckoutBlock',
    },
    templateUrl: '~/afStripe/stripeEmbeddedCheckout.html',
    controller: function ($scope, $element, $q) {
      const ts = $scope.ts = CRM.ts('stripe');

      const listener = (e, data) => this.onAfformSuccess(data);

      this.loadScript = () => $q((resolve, reject) => {
        const endpoint = 'https://js.stripe.com/v3/';

        const scriptTag = document.createElement('script');
        scriptTag.setAttribute('src', endpoint);

        scriptTag.addEventListener('load', resolve);
        scriptTag.addEventListener('error', reject);

        $element[0].append(scriptTag);
      });

      this.$onInit = () => {
        this.loadScript();
        this.getFormElement().on('crmFormSuccess', listener);
      };

      this.$onDestroy = () => {
        this.getFormElement().off('crmFormSuccess', listener);
      }

      this.onAfformSuccess = (data) => {
        const response = data.submissionResponse;
        if (!response || !response.length || !response[0].stripe_embedded_checkout
          || !response[0].stripe_embedded_checkout.client_secret) {
          // invalid response - maybe we switched to a different payment processor
          throw new Error('Missing Stripe Embedded Checkout Client Secret');
        }
        const clientSecret = response[0].stripe_embedded_checkout.client_secret;

        const stripe = this.getStripeClient();

        return stripe.initEmbeddedCheckout({
          fetchClientSecret: () => clientSecret,
        })
        .then((checkout) => {
          const $formElement = this.getFormElement();
          // create target container
          $formElement.hide();
          const $embedContainer = $('<div id="stripe-embed-container" />');
          $embedContainer.insertAfter($formElement);

          // mount the checkout
          checkout.mount('#stripe-embed-container');
        });
      };

      this.getStripeClient = () => {
        const processorConfig = this.afCheckoutBlock.getCheckoutOption();
        if (!processorConfig || !processorConfig.public_key) {
          throw new Error('Could not find Stripe public key when initialising <af-stripe-embedded-checkout>');
        }
        if (!Stripe) {
          throw new Error('Could not find Stripe api library when initialising <af-stripe-embedded-checkout>');
        }

        // fire up the stripe api
        return Stripe(processorConfig.public_key);
      };

      this.getFormElement = () => $element.closest('af-form');
    }
  });
})(angular, CRM.$, CRM._);