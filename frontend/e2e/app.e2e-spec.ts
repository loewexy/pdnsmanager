import { PdnsmanagerPage } from './app.po';

describe('pdnsmanager App', () => {
  let page: PdnsmanagerPage;

  beforeEach(() => {
    page = new PdnsmanagerPage();
  });

  it('should display welcome message', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('Welcome to app!!');
  });
});
