#!/usr/bin/env bash
set -euo pipefail

# Uzupełnij danymi logowania.
login="YOUR_LOGIN"
pass="YOUR_PASSWORD"

if [[ "$login" == "YOUR_LOGIN" || "$pass" == "YOUR_PASSWORD" ]]; then
  echo "Uzupełnij zmienne login i pass w pliku okta_fetch.sh" >&2
  exit 1
fi

base_url="https://arcelik.okta-emea.com"
authorize_url='https://arcelik.okta-emea.com/oauth2/v1/authorize?client_id=okta.2b1959c8-bcc0-56eb-a589-cfcfb7422f26&code_challenge=PgpvH9uwEFyESRTVT_Z-F_kNXWsSBz8mdmP0hyk6RGY&code_challenge_method=S256&nonce=57cRjuEV5z48yL08QoutsjdMrHTuHUQxtFptaW9SAnH3746EkCiE2o275MjsNIcl&redirect_uri=https%3A%2F%2Farcelik.okta-emea.com%2Fenduser%2Fcallback&response_type=code&state=CTMr1LoHzERaECK8izTFb0YvFWvsBfGNWSsajxFWuyXAaNYfKsTxfBhDQ26RE7vG&scope=openid%20profile%20email%20okta.users.read.self%20okta.users.manage.self%20okta.internal.enduser.read%20okta.internal.enduser.manage%20okta.enduser.dashboard.read%20okta.enduser.dashboard.manage%20okta.myAccount.sessions.manage%20okta.internal.navigation.enduser.read'

auth_payload=$(printf '{"username":"%s","password":"%s","options":{"warnBeforePasswordExpired":true,"multiOptionalFactorEnroll":false}}' "$login" "$pass")
auth_response=$(curl -sS -X POST "$base_url/api/v1/authn" \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  --data "$auth_payload")

session_token=$(python3 -c 'import json,sys; d=json.load(sys.stdin); print(d.get("sessionToken",""))' <<<"$auth_response")
status=$(python3 -c 'import json,sys; d=json.load(sys.stdin); print(d.get("status",""))' <<<"$auth_response")

if [[ -z "$session_token" ]]; then
  echo "Nie udało się pobrać sessionToken. Status: $status" >&2
  echo "$auth_response" >&2
  exit 1
fi

separator='&'
[[ "$authorize_url" == *\?* ]] || separator='?'
final_url="${authorize_url}${separator}sessionToken=${session_token}"

curl -sS -L "$final_url" -o okta_page.html

echo "Zapisano treść strony do: okta_page.html"
