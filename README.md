# Agentic Commerce for Magento 2 (ACP Feed, Payments and Checkout)

This module makes your store compatible with AI-powered shopping experiences like OpenAI's "Instant Checkout" in ChatGPT.

This is the first Open-Source module that enables Agentic Commerce features in Magento 2 / Adobe Commerce stores (such as Shop in ChatGPT). The module is Hyva and Adobe Commerce Cloud compatible.

[Agentic Commerce Protocol (ACP)](https://www.agenticcommerce.dev/) is an open standard that enables a conversation between buyers, their AI agents, and businesses to complete a purchase.

**This module is currently actively under development by Magebit and is open to public contributions.**

## Features

- [x] ChatGPT Compatible Product Feed Export
- [x] Instant Checkout Ready
- [x] Agentic Checkout Configuration (according to ACP)
- [x] Agentic Checkout webhooks
- [x] Delegated Payment Support
- [ ] Unit and integration tests

## Requirements

- PHP >= 8.2
- Stripe Payments (only if using checkout)

## Installation

### As a composer package

```
composer require magebitcom/magento2-module-agentic-commerce
```

Run `bin/magento setup:upgrade`

### As a module

1. Download latest release files and extract them under `app/code/Magebit/AgenticCommerce`
2. Run `bin/magento setup:upgrade`

## Configuration

You can find the Module's configuration under `Stores -> Settings -> Configuration -> Magebit -> Agentic Commerce`:
Make sure to configure Product Feed settings before running an export.

<img width="1486" height="864" alt="image" src="https://github.com/user-attachments/assets/14ae49c3-33be-45e0-b539-89856997f557" />


Additionally, there are settings at the product level. Here you can configure product visibility in Agentic search and whether to allow
Agentic Checkout.

<img width="1799" height="320" alt="image" src="https://github.com/user-attachments/assets/5cc8b68f-5ec2-40a4-b0ca-fd806ef38b2d" />

## Wiki

For detailed documentation, see the GitHub Wiki:

- [Product Feed](https://github.com/magebitcom/magento2-agentic-commerce-module/wiki/ProductFeed)
- [Agentic Checkout](https://github.com/magebitcom/magento2-agentic-commerce-module/wiki/AgenticCheckout)
- [Delegated Payments](https://github.com/magebitcom/magento2-agentic-commerce-module/wiki/DelegatedPayments)
- [Webhooks](https://github.com/magebitcom/magento2-agentic-commerce-module/wiki/Webhooks)

## Contributing

Found a bug, have a feature suggestion or just want to help in general? Contributions are very welcome! Check out the list of active issues or submit one yourself.

## Implementation as a Service

Interested in [Magebit](https://magebit.com) doing the integration for you? Contact us on our [website](https://magebit.com/contact) and we can help you with the full implementation of Agentic Commerce sales channels (Instant Checkout on ChatGPT, Product Feeds, etc).

---
![Magebit](https://github.com/user-attachments/assets/cdc904ce-e839-40a0-a86f-792f7ab7961f)

Magebit - Full-service e-commerce agency
[magebit.com](https://magebit.com)
