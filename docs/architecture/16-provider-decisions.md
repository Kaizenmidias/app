# Provider comparisons and approval worksheet

Status: decision-gated proposals; public primary documentation reviewed on 2026-10-20. No account capability, credential, rate allocation, region or price was verified for the operator. No provider was connected. Recheck capabilities/policies at implementation time.

## WhatsApp options

| Option | Architecture fit | Operational tradeoff and decision |
| --- | --- | --- |
| Direct official Meta Cloud API | Owned WABA/number mapping, verified webhooks, templates and server adapter; no browser QR flow invented | Platform/onboarding approvals, account ownership and support responsibility must be verified. Preferred candidate if operator can manage official onboarding; not selected automatically |
| Official provider such as Twilio | Managed official WhatsApp API and account support through BSP adapter | Added provider dependency and account/cost/data handling; validate per-partner onboarding/resource segregation and actual region capabilities |
| New isolated Evolution with official Cloud API transport | Gateway can support an official transport according to its project documentation | More gateway operations; official transport must be distinguished from Baileys; does not justify reusing another project's instance |
| Baileys/WhatsApp Web transport | Session-based client behavior, different contract from official Business Platform | Product inference: unsupported/changed web sessions, reconnection, account restriction and policy/support risk; cannot promise reliability or freedom from blocking. Requires explicit exceptional approval, not default |

Twilio documents the customer-service conversation window and approved-template conditions; those rules belong in the chosen adapter, not scattered UI conditionals. No pricing/messaging cap is assumed from a public page. See [Twilio key concepts](https://www.twilio.com/docs/whatsapp/key-concepts) and [opt-in/API overview](https://www.twilio.com/docs/whatsapp/api).

The [WhatsApp Business Messaging Policy](https://whatsappbusiness.com/policy/) is the policy reference for eligibility, permission and opt-out review. A scraped phone number is not an authorization to market. The direct Meta API documentation URL returned HTTP 429 during this audit; its exact current onboarding/signature/API-version details were **not fully verified** and must be checked before I02: [Meta Cloud API overview](https://developers.facebook.com/docs/whatsapp/cloud-api/overview).

Evolution's own [connection documentation in its repository](https://github.com/evolution-foundation/evolution-api-baileys/blob/main/README.md) describes both Baileys-based and official Cloud API types. Do not equate the Evolution brand with an official transport. No existing CRM Evolution account/number/key/volume/network is approved for this product.

## Managed marketing email alternatives

| Candidate | Documented capability relevant here | Product assessment / missing decision |
| --- | --- | --- |
| Brevo | Marketing event webhooks include campaign feedback such as unsubscribe/spam | Good candidate when managed campaign/list tools are desired. Verify sender/domain scope, API authentication/webhook validation, region, partner isolation and current commercial terms |
| Amazon SES | Sending notifications and list/subscription/suppression features | Fits a queue-centric custom campaign engine; operator must manage AWS IAM/region/reputation and account readiness. More application orchestration is a design inference, not a benchmark |
| Resend | Email/contact/domain webhook events and contact/Broadcast tooling | Candidate for API-first developer flow; verify selected product support for required marketing/unsubscribe, sender segregation and current account eligibility |

Primary references: [Brevo marketing webhooks](https://developers.brevo.com/docs/marketing-webhooks), [Brevo quickstart](https://developers.brevo.com/docs/quickstart), [SES event notifications](https://docs.aws.amazon.com/ses/latest/dg/monitor-sending-activity-using-notifications.html), [SES list/subscription management](https://docs.aws.amazon.com/ses/latest/dg/lists-and-subscriptions.html), [SES unsubscribe implementation](https://docs.aws.amazon.com/en_en/ses/latest/dg/sending-email-subscription-management.html), [Resend webhook documentation](https://resend.com/docs/webhooks/introduction), [Resend contact/Broadcast introduction](https://resend.com/blog/manage-subscribers-using-resend-audiences).

No provider is cheapest/best for this account by evidence available. Compare account-level quotas, domain verification, subaccount/sender ownership, supported event authentication, available region, reputation, support and actual contract/cost before choice. No local bulk SMTP installation is proposed. Keep security/verification/password transactional mail separate from marketing suppression purpose.

## Consent and data handling

Before campaigns, approve a purpose/channel-specific permission policy and retention schedule. Record source, when/how permission was obtained, relevant notice/version and withdrawal. Suppression wins over list membership, cached segments and old recipient snapshots. Reimporting a Lead must not reset opt-out. Recheck eligibility at dispatch, not only audience creation. Provider template/window restrictions and lawful processing are independent checks.

Publicly accessible information does not remove the need for a valid processing purpose/basis. This is a technical control proposal, not a legal conclusion that every campaign requires the same legal basis. The operator/legal reviewer should apply the [ANPD legitimate-interest guidance](https://www.gov.br/anpd/pt-br/centrais-de-conteudo/materiais-educativos-e-publicacoes/guia_orientativo_hipoteses_legais_tratamento_de_dados_pessoais_legitimo_interesse) and [LGPD statutory text](https://www.planalto.gov.br/ccivil_03/_ato2015-2018/2018/lei/l13709.htm). Do not treat a chosen legal basis as permission to violate provider terms.

## Map/geocoding dependencies already in code

Current original JS uses public OpenStreetMap tiles and Nominatim directly from browsers. These are real external dependencies, not Gosom API endpoints. The current [tile policy](https://operations.osmfoundation.org/policies/tiles/) requires attribution and constrains service use/availability. The [public Nominatim policy](https://operations.osmfoundation.org/policies/nominatim/) applies its aggregate application traffic limit, caching/identification requirements and restrictions, including its stated no-code/vibe-platform context. Do not assume a generic SaaS geocoding service is permitted or scalable on the public endpoint. Obtain a deliberate approved compliant provider strategy before expansion; prefer evaluating a contracted provider rather than automatically adding a proxy or new usage.

Do not remove Leaflet/OpenStreetMap attribution while removing promotional links. Google Fonts/CDN dependencies also need CSP/privacy/offline-resilience review; no local font package is installed here.

## Native Laravel versus n8n

Use native actions/jobs/outbox for initial bounded automation: same authorization/transactions, no additional service and straightforward tests. n8n is an optional isolated integration if workflows truly require externally operated connectors or user workflow tooling. Choosing it requires a new instance/credentials/resource audit and signed, constrained event interface; never direct DB credentials or reuse of another project's workflows. Provider/tool isolation is an architecture decision, not an instruction to start an instance. See [n8n security audit documentation](https://docs.n8n.io/deploy/host-n8n/configure-n8n/security/run-security-audits.md).

## Mandatory provider worksheet (complete in I01)

- Approved provider, official transport and contract/API version.
- Account owner, partner resource mapping, onboarding permissions and revocation.
- Test/live/sandbox capabilities and actual country/region/method availability.
- Authentication, webhook signature/replay scheme and event identifier/order semantics.
- Idempotency support/retention and safe unknown-outcome reconciliation.
- Sender/number/domain verification and template lifecycle.
- Opt-in/opt-out/purpose evidence and suppression propagation.
- Provider operational rates/cost attribution; platform budget and failure circuit.
- Private media handling, retention, data location and legal review.
- Monitoring/support and recovery procedures.
- Date, approver, evidence links and separately authorized implementation scope.
