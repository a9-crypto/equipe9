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
  setup() {
    const tickets = ref([]);
    const chargement = ref(true);
    const erreur = ref('');

    onMounted(async () => {
      try {
        tickets.value = await api('GET', '/api/tickets');
      } catch (e) {
        erreur.value = e.message;
      } finally {
        chargement.value = false;
      }
    });

    return { tickets, chargement, erreur, ...outils };
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
            <tr v-for="t in tickets" :key="t.id">
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
  { motif: /^#?\/?$/, vue: ListeTickets },
];

// Liens de la barre de navigation
const MENU = [
  { href: '#/', label: 'Tickets', icone: 'bi-list-ul' },
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

    return {
      route,
      erreur,
      menu: MENU,
      meta: computed(() => etat.meta),
      utilisateur: computed(() => etat.meta?.users.find((u) => u.id === 1)),
      vue: computed(() => correspondance.value.vue),
      params: computed(() => correspondance.value.params),
    };
  },
}).mount('#app');
