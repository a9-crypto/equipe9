/*
 * Application Vue 3 (sans build) du socle Mantis-lite.
 * - api()      : appels à l'API JSON (src/api.php)
 * - outils     : libellés, couleurs et dates
 * - vues       : un objet par écran, déclaré dans ROUTES
 * Pour ajouter un écran : créer un composant, puis l'ajouter à ROUTES et, si besoin, à MENU.
 */

const { createApp, reactive, ref, computed, onMounted } = Vue;

// ---------------------------------------------------------------------------
// API
// ---------------------------------------------------------------------------

async function api(methode, chemin, corps) {
  const reponse = await fetch(chemin, {
    method: methode,
    headers: corps ? { 'Content-Type': 'application/json' } : {},
    body: corps ? JSON.stringify(corps) : undefined,
  });
  const donnees = await reponse.json().catch(() => ({}));
  if (!reponse.ok) {
    throw new Error(donnees.erreur || `Erreur ${reponse.status}`);
  }
  return donnees;
}

// ---------------------------------------------------------------------------
// État partagé et outils
// ---------------------------------------------------------------------------

// Métadonnées chargées une fois : listes de valeurs MantisBT, projets, catégories, utilisateurs
const etat = reactive({ meta: null });

const outils = {
  libelle(liste, id) {
    return etat.meta?.enums[liste]?.find((e) => e.id === id)?.label ?? '—';
  },
  couleurStatut(id) {
    return etat.meta?.enums.status.find((e) => e.id === id)?.color ?? '#eee';
  },
  date(ts) {
    return ts ? new Date(ts * 1000).toLocaleDateString('fr-FR') : '—';
  },
  dateHeure(ts) {
    return ts ? new Date(ts * 1000).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) : '—';
  },
  depuis(ts) {
    const secondes = Math.floor(Date.now() / 1000) - ts;
    if (secondes < 3600) return `il y a ${Math.max(1, Math.floor(secondes / 60))} min`;
    if (secondes < 86400) return `il y a ${Math.floor(secondes / 3600)} h`;
    return `il y a ${Math.floor(secondes / 86400)} j`;
  },
};

// ---------------------------------------------------------------------------
// Vues
// ---------------------------------------------------------------------------

const ListeTickets = {
  props: ["params"],
  setup(props) {
    const tickets = ref([]);
    const chargement = ref(true);
    const erreur = ref('');

    onMounted(async () => {
      try {
        // Filtres venant de l'adresse (ex. #/?handler_id=none), transmis tels quels à l'API
        tickets.value = await api('GET', '/api/tickets' + (props.params[0] ? '?' + props.params[0] : ''));
      } catch (e) {
        erreur.value = e.message;
      } finally {
        chargement.value = false;
      }
    });

    const ouvrir = (id) => { location.hash = `#/ticket/${id}`; };

    return { tickets, chargement, erreur, ouvrir, ...outils };
  },
  template: `
    <div class="d-flex align-items-center mb-3">
      <h1 class="h4 mb-0">Tickets</h1>
      <span class="badge text-bg-secondary ms-2">{{ tickets.length }}</span>
    </div>
    <div v-if="erreur" class="alert alert-danger">{{ erreur }}</div>
    <div v-else-if="chargement" class="text-secondary">Chargement…</div>
    <div v-else class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover table-tickets mb-0">
          <thead class="table-light">
            <tr>
              <th>N°</th><th>Résumé</th><th>Projet</th><th>Statut</th>
              <th>Priorité</th><th>Responsable</th><th>Mis à jour</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in tickets" :key="t.id" @click="ouvrir(t.id)">
              <td class="text-secondary">#{{ t.id }}</td>
              <td>{{ t.summary }}</td>
              <td class="small">{{ t.project }}<div class="text-secondary">{{ t.category }}</div></td>
              <td>
                <span class="badge badge-statut" :style="{ background: couleurStatut(t.status) }">
                  {{ libelle('status', t.status) }}
                </span>
              </td>
              <td :class="'priorite-' + t.priority">{{ libelle('priority', t.priority) }}</td>
              <td>{{ t.handler || '—' }}</td>
              <td class="small text-secondary" :title="dateHeure(t.last_updated)">{{ depuis(t.last_updated) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  `,
};

const TableauDeBord = {
  props: ["params"],
  setup() {
    const stats = ref(null);
    const erreur = ref('');

    onMounted(async () => {
      try {
        stats.value = await api('GET', '/api/stats');
      } catch (e) {
        erreur.value = e.message;
      }
    });

    const parStatut = computed(() => etat.meta.enums.status.map((s) => ({ ...s, n: stats.value.by_status[s.id] ?? 0 })));
    const parPriorite = computed(() => [...etat.meta.enums.priority].reverse()
      .map((p) => ({ ...p, n: stats.value.by_priority[p.id] ?? 0 })).filter((p) => p.n > 0));

    return { stats, erreur, parStatut, parPriorite, ...outils };
  },
  template: `
    <h1 class="h4 mb-3">Tableau de bord</h1>
    <div v-if="erreur" class="alert alert-danger">{{ erreur }}</div>
    <div v-else-if="!stats" class="text-secondary">Chargement…</div>
    <template v-else>
      <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
          <a href="#/" class="text-decoration-none text-body">
            <div class="card text-center shadow-sm"><div class="card-body">
              <div class="display-6">{{ stats.total }}</div><div class="text-secondary">Tickets au total</div>
            </div></div>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="#/?handler_id=none" class="text-decoration-none text-body">
            <div class="card text-center shadow-sm border-warning"><div class="card-body">
              <div class="display-6">{{ stats.unassigned }}</div><div class="text-secondary">Non affectés</div>
            </div></div>
          </a>
        </div>
        <div class="col-6 col-md-3">
          <a href="#/?overdue=1" class="text-decoration-none text-body">
            <div class="card text-center shadow-sm border-danger"><div class="card-body">
              <div class="display-6 text-danger">{{ stats.overdue }}</div><div class="text-secondary">En retard</div>
            </div></div>
          </a>
        </div>
      </div>
      <div class="row g-3">
        <div class="col-md-7">
          <div class="card shadow-sm">
            <div class="card-header">Tickets par statut</div>
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between" v-for="s in parStatut" :key="s.id">
                <a :href="'#/?status=' + s.id" class="text-decoration-none text-body">{{ s.label }}</a>
                <span class="badge badge-statut" :style="{ background: s.color }">{{ s.n }}</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-md-5">
          <div class="card shadow-sm">
            <div class="card-header">Tickets ouverts par priorité</div>
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between" v-for="p in parPriorite" :key="p.id">
                <span :class="'priorite-' + p.id">{{ p.label }}</span>
                <span class="badge text-bg-secondary">{{ p.n }}</span>
              </li>
              <li class="list-group-item text-secondary" v-if="!parPriorite.length">Aucun ticket ouvert.</li>
            </ul>
          </div>
        </div>
      </div>
    </template>
  `,
};

const FicheTicket = {
  props: ["params"],
  setup(props) {
    const ticket = ref(null);
    const erreur = ref('');
    const message = ref('');
    const enCours = ref(false);
    const form = reactive({ handler_id: '', status: 10 });

    const remplir = (t) => {
      ticket.value = t;
      form.handler_id = t.handler_id ?? '';
      form.status = t.status;
    };

    onMounted(async () => {
      try {
        remplir(await api('GET', `/api/tickets/${props.params[0]}`));
      } catch (e) {
        erreur.value = e.message;
      }
    });

    async function enregistrer() {
      enCours.value = true;
      erreur.value = '';
      message.value = '';
      try {
        // Seuls les champs modifiés sont envoyés : l'API passe alors un ticket « Nouveau » à « Affecté » toute seule
        const corps = {};
        if (form.handler_id !== (ticket.value.handler_id ?? '')) {
          corps.handler_id = form.handler_id === '' ? null : Number(form.handler_id);
        }
        if (Number(form.status) !== ticket.value.status) {
          corps.status = Number(form.status);
        }
        if (Object.keys(corps).length === 0) {
          message.value = 'Aucune modification.';
          return;
        }
        remplir(await api('PATCH', `/api/tickets/${ticket.value.id}`, corps));
        message.value = 'Modifications enregistrées.';
      } catch (e) {
        erreur.value = e.message;
      } finally {
        enCours.value = false;
      }
    }

    const techniciens = computed(() => etat.meta.users);
    return { ticket, erreur, message, enCours, form, enregistrer, techniciens, statuts: computed(() => etat.meta.enums.status), ...outils };
  },
  template: `
    <a href="#/" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Retour aux tickets</a>
    <div v-if="erreur && !ticket" class="alert alert-danger mt-3">{{ erreur }}</div>
    <div v-else-if="!ticket" class="text-secondary mt-3">Chargement…</div>
    <div v-else class="row g-3 mt-1">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-header"><strong>#{{ ticket.id }}</strong> · {{ ticket.summary }}</div>
          <div class="card-body">
            <p class="text-secondary small mb-1">
              {{ ticket.project }}<span v-if="ticket.category"> · {{ ticket.category }}</span>
              · déclaré par {{ ticket.reporter }} le {{ dateHeure(ticket.date_submitted) }}
            </p>
            <p class="mb-3" style="white-space: pre-wrap">{{ ticket.description }}</p>
            <hr>
            <h2 class="h6">Historique</h2>
            <ul class="small text-secondary mb-0">
              <li v-for="h in ticket.history" :key="h.id">
                {{ dateHeure(h.date_modified) }} · {{ h.user }} :
                <template v-if="h.type === 1">ticket créé</template>
                <template v-else-if="h.type === 2">note ajoutée</template>
                <template v-else-if="h.field_name === 'status'">statut {{ libelle('status', Number(h.old_value)) }} → {{ libelle('status', Number(h.new_value)) }}</template>
                <template v-else-if="h.field_name === 'handler_id'">responsable modifié</template>
                <template v-else>{{ h.field_name }} modifié</template>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm">
          <div class="card-header">Traitement</div>
          <form class="card-body" @submit.prevent="enregistrer">
            <div v-if="erreur" class="alert alert-danger py-2">{{ erreur }}</div>
            <div v-if="message" class="alert alert-success py-2">{{ message }}</div>
            <label class="form-label" for="responsable">Responsable</label>
            <select id="responsable" class="form-select mb-3" v-model="form.handler_id">
              <option value="">— Non affecté —</option>
              <option v-for="u in techniciens" :key="u.id" :value="u.id">{{ u.realname }}</option>
            </select>
            <label class="form-label" for="statut">Statut</label>
            <select id="statut" class="form-select mb-3" v-model="form.status">
              <option v-for="s in statuts" :key="s.id" :value="s.id">{{ s.label }}</option>
            </select>
            <div class="mb-1 small text-secondary">Priorité : <span :class="'priorite-' + ticket.priority">{{ libelle('priority', ticket.priority) }}</span></div>
            <div class="mb-3 small text-secondary">Résolution : {{ libelle('resolution', ticket.resolution) }}</div>
            <button class="btn btn-primary w-100" :disabled="enCours"><i class="bi bi-check2 me-1"></i>Enregistrer</button>
          </form>
        </div>
      </div>
    </div>
  `,
};

const PageIntrouvable = {
  props: ["params"],
  template: `
    <div class="text-center py-5">
      <h1 class="h4">Page introuvable</h1>
      <a href="#/">Retour aux tickets</a>
    </div>
  `,
};

// ---------------------------------------------------------------------------
// Routage par ancre (#/...)
// ---------------------------------------------------------------------------

// Chaque route : un motif sur l'ancre et un composant. Les groupes capturés arrivent dans params.
const ROUTES = [
  { motif: /^#?\/?(?:\?(.*))?$/, vue: ListeTickets },
  { motif: /^#\/tableau-de-bord$/, vue: TableauDeBord },
  { motif: /^#\/ticket\/(\d+)$/, vue: FicheTicket },
];

// Liens de la barre de navigation
const MENU = [
  { href: '#/', label: 'Tickets', icone: 'bi-list-ul' },
  { href: '#/tableau-de-bord', label: 'Tableau de bord', icone: 'bi-speedometer2' },
];

createApp({
  setup() {
    const route = ref(location.hash || '#/');
    const erreur = ref('');
    window.addEventListener('hashchange', () => { route.value = location.hash || '#/'; });

    onMounted(async () => {
      try {
        etat.meta = await api('GET', '/api/meta');
      } catch (e) {
        erreur.value = `API indisponible : ${e.message}`;
      }
    });

    const correspondance = computed(() => {
      for (const r of ROUTES) {
        const m = route.value.match(r.motif);
        if (m) return { vue: r.vue, params: m.slice(1) };
      }
      return { vue: PageIntrouvable, params: [] };
    });

    // « Tickets » reste actif pour la liste (avec ou sans filtres) et pour la fiche d'un ticket
    const actif = (lien) => (lien.href === '#/'
      ? /^#?\/?(\?.*)?$/.test(route.value) || route.value.startsWith('#/ticket/')
      : route.value.startsWith(lien.href));

    return {
      route,
      actif,
      erreur,
      menu: MENU,
      meta: computed(() => etat.meta),
      utilisateur: computed(() => etat.meta?.users.find((u) => u.id === 1)),
      vue: computed(() => correspondance.value.vue),
      params: computed(() => correspondance.value.params),
    };
  },
}).mount('#app');
