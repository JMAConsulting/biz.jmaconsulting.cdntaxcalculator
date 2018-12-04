const puppeteer = require("puppeteer");

let browser, page;

describe("public-membership-form", () => {

  // TODO: https://github.com/smooth-code/jest-puppeteer/issues/88#issuecomment-403603386
  beforeAll(async () => {
    browser = await puppeteer.launch({
      headless: process.env.HEADLESS !== 'false',
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox'
      ]
    });

    page = await browser.newPage();
  });

  afterAll(async () => {
    browser.close();
  });

  describe("Cdn Tax Public Membership Form - Anonymous Select Location", () => {
    beforeEach(async () => {
      await page.goto( "https://taxtests.symbiodev.xyz/civicrm/contribute/transact?action=preview&id=1&reset=1", { waitUntil: "networkidle2" } );
      await page.waitForSelector('#crm-cdntaxcalculator-province-popup');
    });

    describe("Cdn Tax Select Location", () => {
      it("Has a popup to select the province and country defaults to Canada", async () => {
        // Basic check on the popup content
        let e = await page.$eval('#crm-cdntaxcalculator-province-popup > h4', function(x) {
          return x.innerText;
        });
        expect(e).toEqual("Please select your billing location:");

        // Ensure that the country field is present
        e = await page.$eval('#crm-cdntaxcalculator-province-popup #billing_country_id-5', function(x) {
          return parseInt(x.value);
        });
        expect(e).toEqual(1039);
      });

      it("Can select Quebec and reload the page correctly", async () => {
        await page.click('#crm-cdntaxcalculator-province-popup #s2id_billing_state_province_id-5');

        // Sketchy way of selecting "Quebec" in the select2 element
        await page.waitForSelector('#select2-result-label-13');
        await page.click('#select2-result-label-13');

        // Click the submit button and make sure it redirects correctly
        await page.click('.ui-dialog .ui-dialog-buttonpane button');
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
        expect(page.url()).toEqual('https://taxtests.symbiodev.xyz/civicrm/contribute/transact?action=preview&id=1&reset=1&cdntax_province_id=1110&cdntax_country_id=1039');

        // One quick test, although we'll test more latest
        let e = await page.$eval('#crm-cdntaxcalculator-pricesetinfo', function(x) {
          return x.innerText;
        });
        expect(e).toContain("Canada, Quebec");
      }, 30000);
    });
  });

  // We're doing on purpose to test for rounding errors
  // 100.05 * 0.14975 = 14.98 (total: 115.03)
  describe("Cdn Tax Public Membership Form - Quebec taxes", () => {
    beforeEach(async () => {
      await page.goto( "https://taxtests.symbiodev.xyz/civicrm/contribute/transact?action=preview&id=1&reset=1&cdntax_province_id=1110&cdntax_country_id=1039", { waitUntil: "networkidle2" } );
      await page.waitForSelector('#crm-container');
    });

    describe("Cdn Tax Amounts", () => {
      it("Has the correct total amount for Quebec", async () => {
        let e = await page.$eval('#priceset .price-set-row.membership-row1 .crm-price-amount-amount', function(x) {
          return x.innerText;
        });
        expect(e).toEqual("$ 115.03");
      });

      it("Has the correct tax amount for Quebec", async () => {
        let e = await page.$eval('#priceset .price-set-row.membership-row1 .crm-price-amount-tax', function(x) {
          return x.innerText.trim();
        });
        expect(e).toEqual("(includes Sales Tax of $ 14.98)");
      });

    });

    // TODO
    // - ensure that logged-in user (or with checksum, avoids login), has province/taxes set to the billing location
    // - test that we can change the current province to another province (test without passing the province_id in the URL, to test the cookie)
    // - validate the confirmation page

  });

});
