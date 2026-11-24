# 6. Banks, cards, promotions, and installments

From the **Payway** sidebar, load the cards and banks you will use and create at least one **active and applicable** promotion so the payment method appears at checkout.

You can also use **Payway → Setup** to create the “All” bank, select Decidir catalog cards, and generate default promotions (including MiPyME 3→13 / 6→16).

![Promotions / default catalog](assets/06-banks-cards-promos.png)

## Banks

If you do not need a per-bank promotion, group everything under **All** (or “All banks”). That bank is typically used when building the promotion.

CRUD: list / add / edit / delete (nonce + `manage_woocommerce` capability). Admin lists are paginated and ordered by name.

## Cards

Each card has `id_sps` (= `payment_method_id` sent to Decidir) and `id_nps`. Common catalog:

| Card | ID SPS | ID NPS |
|---|---|---|
| Visa Credit | 1 | 1 |
| Visa Debit | 31 | 31 |
| Visa Prepaid | 114 | 114 |
| MasterCard Credit | 104 | 104 |
| MasterCard Debit | 105 | 105 |
| MasterCard Prepaid | 116 | 116 |
| Maestro | 106 | 106 |
| Cabal Credit | 63 | 63 |
| Cabal Debit | 108 | 108 |
| AMEX | 111 | 111 |
| Amex Pura | 65 | 65 |
| Discover | 139 | 139 |
| Naranja | 24 | 24 |
| Tarjeta Sol | 143 | 143 |

Note: Amex Pura (`65`) usually applies in Decidir 2.0, not Ventas Online / D+.

## Promotions

Configure **one promotion per card type** (Visa Debit, Visa Credit, Amex, etc.). For most merchants this defines **checkout installment plans**, not a real issuer bank promo.

It must be **active** and **applicable** on the charge day.

### No expiry

**No expiry** checkbox (`no_expiry`, on by default in seeds): end date is not validated. If unchecked, set `to_date`.

Without at least one applicable promotion, the method **does not appear** at checkout.

### Common installment values (`fee_to_send`)

| Plan | `fee_to_send` |
|---|---|
| 1 installment (required for debit) | 1 |
| Cuota Simple 3 | 13 |
| Cuota Simple 6 | 16 |

Debit cards without a 1-installment plan are flagged in Setup / Status.
