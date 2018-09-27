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

  describe("Cdn Tax Public Membership Form", () => {
    beforeEach(async () => {
      await page.goto( "https://taxtests.symbiodev.xyz/civicrm/contribute/transact?action=preview&id=1&reset=1", { waitUntil: "networkidle2" } );
    });

    describe("Cdn Tax Select Location", () => {

      it("Has a popup to select the province and country defaults to Canada", async () => {
        await page.waitForSelector('#crm-cdntaxcalculator-province-popup');

        // Basic check on the popup content
        let e = await page.$eval('#crm-cdntaxcalculator-province-popup > h4', function(x) {
          return x.innerText;
        });
        expect(e).toEqual("Please select your billing location:");

        // Ensure that the country field is present
        let e = await page.$eval('#crm-cdntaxcalculator-province-popup #billing_country_id-5', function(x) {
          return x.value;
        });
        expect(e).toEqual(1039);
      });

    });
  });

});
