<script>
    document.addEventListener('DOMContentLoaded', () => {

        function onlyDigits(v) {
            return (v || '').replace(/\D+/g, '');
        }

        // ---------
        // Máscaras BR
        // ---------
        function maskCPF(v) {
            v = onlyDigits(v).slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            return v;
        }

        function maskCNPJ(v) {
            v = onlyDigits(v).slice(0, 14);
            v = v.replace(/^(\d{2})(\d)/, '$1.$2');
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
            v = v.replace(/(\d{4})(\d{1,2})$/, '$1-$2');
            return v;
        }

        function maskCEP(v) {
            v = onlyDigits(v).slice(0, 8);
            v = v.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
            return v;
        }

        // -------------------------------
        // Telefone/celular internacional (E.164) + visual BR amigável
        //
        // - Sempre valida E.164: 10..15 dígitos
        // - Se começar com 55:
        //    celular: +55 11 97351-8600  (55 + DDD2 + 9 dígitos => 13)
        //    fixo:    +55 11 7351-8600   (55 + DDD2 + 8 dígitos => 12)
        // - Outros países: +<dígitos> (sem formatação especial)
        // -------------------------------
        function maskE164Raw(v, maxDigits = 15) {
            let d = onlyDigits(v).slice(0, maxDigits);
            return d.length ? ('+' + d) : '';
        }

        function isValidE164(v) {
            const d = onlyDigits(v);
            return d.length >= 10 && d.length <= 15;
        }

        // extra BR (quando country code = 55)
        function isValidBRMobileDigits(d) {
            // 55 + DDD(2) + 9 dígitos = 13 e começa com 9 no início do número (index 4)
            return d.length === 13 && d.startsWith('55') && d[4] === '9';
        }
        function isValidBRPhoneDigits(d) {
            // 55 + DDD(2) + 8 dígitos = 12 e NÃO começa com 9 no início do número (index 4)
            return d.length === 12 && d.startsWith('55') && d[4] !== '9';
        }

        function formatBRMobileDisplay(d) {
            // d = "55" + DDD(2) + 9 dígitos => 13
            const cc = '55';
            const rest = d.slice(2);     // DDD + número
            const ddd  = rest.slice(0, 2);
            const num  = rest.slice(2);  // 9 dígitos (ex: 973518600)

            const p1 = num.slice(0, 5);  // 97351
            const p2 = num.slice(5, 9);  // 8600

            let out = `+${cc}`;
            if (ddd.length) out += ` ${ddd}`;
            if (p1.length) out += ` ${p1}`;
            if (p2.length) out += `-${p2}`;
            return out;
        }

        function formatBRPhoneDisplay(d) {
            // d = "55" + DDD(2) + 8 dígitos => 12
            const cc = '55';
            const rest = d.slice(2);
            const ddd  = rest.slice(0, 2);
            const num  = rest.slice(2); // 8 dígitos (ex: 73518600)

            const p1 = num.slice(0, 4); // 7351
            const p2 = num.slice(4, 8); // 8600

            let out = `+${cc}`;
            if (ddd.length) out += ` ${ddd}`;
            if (p1.length) out += ` ${p1}`;
            if (p2.length) out += `-${p2}`;
            return out;
        }

        function maskMobileInt(v) {
            const d = onlyDigits(v).slice(0, 15);
            if (!d.length) return '';
            if (d.startsWith('55')) return formatBRMobileDisplay(d.slice(0, 13)); // limita BR mobile
            return maskE164Raw(d, 15);
        }

        function maskPhoneInt(v) {
            const d = onlyDigits(v).slice(0, 15);
            if (!d.length) return '';
            if (d.startsWith('55')) return formatBRPhoneDisplay(d.slice(0, 12)); // limita BR fixo
            return maskE164Raw(d, 15);
        }

        function isValidMobileInt(v) {
            if (!isValidE164(v)) return false;
            const d = onlyDigits(v);
            if (d.startsWith('55')) return isValidBRMobileDigits(d);
            return true;
        }

        function isValidPhoneInt(v) {
            if (!isValidE164(v)) return false;
            const d = onlyDigits(v);
            if (d.startsWith('55')) return isValidBRPhoneDigits(d);
            return true;
        }

        // ----------
        // UI erro inline
        // ----------
        function setFieldError(el, msg) {
            el.classList.add('border-red-500');
            el.classList.remove('border-zinc-800');

            let help = el.parentElement.querySelector('.js-field-error');
            if (!help) {
                help = document.createElement('div');
                help.className = 'js-field-error text-xs text-red-300 mt-2';
                el.parentElement.appendChild(help);
            }
            help.textContent = msg;
            el.setCustomValidity(msg || '');
        }

        function clearFieldError(el) {
            el.classList.remove('border-red-500');
            el.classList.add('border-zinc-800');

            const help = el.parentElement.querySelector('.js-field-error');
            if (help) help.remove();

            el.setCustomValidity('');
        }

        // -------------------------
        // Validadores (CPF / CNPJ)
        // -------------------------
        function isValidCPF(cpf) {
            cpf = onlyDigits(cpf);
            if (cpf.length !== 11) return false;
            if (/^(\d)\1{10}$/.test(cpf)) return false;

            let sum = 0;
            for (let i = 0; i < 9; i++) sum += parseInt(cpf[i]) * (10 - i);
            let d1 = (sum * 10) % 11;
            if (d1 === 10) d1 = 0;
            if (d1 !== parseInt(cpf[9])) return false;

            sum = 0;
            for (let i = 0; i < 10; i++) sum += parseInt(cpf[i]) * (11 - i);
            let d2 = (sum * 10) % 11;
            if (d2 === 10) d2 = 0;
            return d2 === parseInt(cpf[10]);
        }

        function isValidCNPJ(cnpj) {
            cnpj = onlyDigits(cnpj);
            if (cnpj.length !== 14) return false;
            if (/^(\d)\1{13}$/.test(cnpj)) return false;

            const calc = (base) => {
                let size = base.length;
                let pos = size - 7;
                let sum = 0;

                for (let i = size; i >= 1; i--) {
                    sum += parseInt(base[size - i]) * pos--;
                    if (pos < 2) pos = 9;
                }
                const res = sum % 11;
                return (res < 2) ? 0 : 11 - res;
            };

            const d1 = calc(cnpj.slice(0, 12));
            const d2 = calc(cnpj.slice(0, 12) + d1);
            return cnpj === (cnpj.slice(0, 12) + String(d1) + String(d2));
        }

        // ----------
        // Helpers pra preencher por key
        // ----------
        function setByKey(key, value) {
            if (value == null) return;

            const el =
                document.querySelector(`[data-key="${key}"]`) ||
                document.querySelector(`[name="${key}"]`) ||
                document.querySelector(`[name="f[${key}]"]`);

            if (!el) return;

            if (el.value === '' || el.value == null) {
                el.value = value;
                el.dispatchEvent(new Event('input', {bubbles: true}));
            }
        }

        // ----------
        // Delegação de máscara
        // ----------
        document.addEventListener('input', (e) => {
            const el = e.target;
            if (!el || !el.matches || !el.matches('[data-mask]')) return;

            const type = el.getAttribute('data-mask');
            const cur = el.value;

            if (type === 'cpf') el.value = maskCPF(cur);
            if (type === 'cnpj') el.value = maskCNPJ(cur);
            if (type === 'cep') el.value = maskCEP(cur);

            if (type === 'mobile_int') el.value = maskMobileInt(cur);
            if (type === 'phone_int')  el.value = maskPhoneInt(cur);

            if (type === 'cpf' || type === 'cnpj' || type === 'mobile_int' || type === 'phone_int') {
                clearFieldError(el);
            }
        });

        // ----------
        // Validação no blur
        // ----------
        document.addEventListener('blur', (e) => {
            const el = e.target;
            if (!el || !el.matches) return;

            const mask = el.getAttribute('data-mask');

            if (mask === 'cpf') {
                const digits = onlyDigits(el.value);
                if (digits.length === 0) return clearFieldError(el);
                if (digits.length < 11) return setFieldError(el, 'CPF incompleto.');
                if (!isValidCPF(digits)) return setFieldError(el, 'CPF inválido.');
                return clearFieldError(el);
            }

            if (mask === 'cnpj') {
                const digits = onlyDigits(el.value);
                if (digits.length === 0) return clearFieldError(el);
                if (digits.length < 14) return setFieldError(el, 'CNPJ incompleto.');
                if (!isValidCNPJ(digits)) return setFieldError(el, 'CNPJ inválido.');
                return clearFieldError(el);
            }

            if (mask === 'mobile_int') {
                if ((el.value || '').trim() === '') return clearFieldError(el);
                if (!isValidMobileInt(el.value)) return setFieldError(el, 'Celular inválido. Use +<código><número> (ex: +244912345678)');
                return clearFieldError(el);
            }

            if (mask === 'phone_int') {
                if ((el.value || '').trim() === '') return clearFieldError(el);
                if (!isValidPhoneInt(el.value)) return setFieldError(el, 'Telefone inválido. Use +<código><número> (ex: +244212345678)');
                return clearFieldError(el);
            }
        }, true);

        // ----------
        // ViaCEP (quando sai do CEP)
        // ----------
        async function viaCepLookup(cep) {
            cep = onlyDigits(cep);
            if (cep.length !== 8) return null;
            const r = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const j = await r.json();
            if (j.erro) return null;
            return j;
        }

        document.addEventListener('blur', async (e) => {
            const el = e.target;
            if (!el || !el.matches || !el.matches('[data-autofill="cep"]')) return;

            const data = await viaCepLookup(el.value);
            if (!data) return;

            document.querySelectorAll('[data-cep-target]').forEach(target => {
                const k = target.getAttribute('data-cep-target');
                if (!k) return;

                const val = data[k];
                if (val == null) return;

                if (target.value === '' || target.value == null) {
                    target.value = val;
                    target.dispatchEvent(new Event('input', {bubbles: true}));
                }
            });
        }, true);

        // ----------
        // API CNPJ (quando sai do CNPJ)
        // ----------
        document.addEventListener('blur', async (e) => {
            const el = e.target;
            if (!el || !el.matches || !el.matches('[data-autofill="cnpj"]')) return;

            const cnpj = onlyDigits(el.value);
            if (cnpj.length !== 14) return;

            const r = await fetch(`/api/cnpj?cnpj=${cnpj}`);
            const j = await r.json();
            if (!r.ok || j.code !== 'success') return;

            setByKey('ins_instituicao', j.data.razao_social);
            setByKey('ins_endereco', j.data.logradouro);
            setByKey('ins_numero', j.data.numero);
            setByKey('ins_bairro', j.data.bairro);
            setByKey('ins_cidade', j.data.cidade);
            setByKey('ins_estado', j.data.estado);
            setByKey('ins_cep', j.data.cep);
            setByKey('ins_pais', j.data.pais || 'Brasil');
        }, true);

        // ----------
        // Reaplica máscaras ao carregar (valores vindos do banco/old)
        // ----------
        document.querySelectorAll('[data-mask="mobile_int"], [data-mask="phone_int"]').forEach((el) => {
            const t = el.getAttribute('data-mask');
            el.value = (t === 'mobile_int') ? maskMobileInt(el.value) : maskPhoneInt(el.value);
        });

        // ----------
        // Antes de enviar: normaliza tel/cel pra +<dígitos> (sem espaços/traços)
        // ----------
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form || !form.querySelectorAll) return;

            form.querySelectorAll('input[data-mask="mobile_int"], input[data-mask="phone_int"]').forEach((inp) => {
                const d = onlyDigits(inp.value);
                inp.value = d ? ('+' + d) : '';
            });
        }, true);

    });
</script>
