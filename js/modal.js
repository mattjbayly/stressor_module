(function (Drupal) {
    Drupal.behaviors.stressorModal = {
      attach: function (context) {
        // Only run once on the full page load:
        if (context !== document) {
          return;
        }
        // Use localStorage so it won’t reappear across page reloads or tabs.
        if (localStorage.getItem('stressorModalShown')) {
          return;
        }
  
        // Create overlay + modal in the DOM:
        const overlay = document.createElement('div');
        overlay.id = 'stressor-modal-overlay';
        overlay.innerHTML = `
          <div id="stressor-modal">
            <button class="close-btn" aria-label="Close">&times;</button>
            <h4>An Online Database of Stressor-Response Functions for Native Trout and Salmon</h4>
            <b>Authors and Contributors: Matthew Bayly; Sierra Sullivan; Matthew Bakken; Jordan Rosenfeld; Paxton Calhoun; Aimee Fullerton; Morgan Bond; Sara Tobias; Alex Tekatch</b>
            <p>Stressor-response functions (or dose-response relationships) are fundamental components of ecotoxicology (and ecology more broadly) as they help to define and predict the context dependence of ecological states and processes.  Many researchers and conservation practitioners are routinely required to scour the primary literature to identify and compile quantitative stressor-response functions for various applications. Stressor-response functions are used in cumulative effect assessments, predictive modelling, and many other forms of impact assessments. For example, one or more stressor-response functions are often used as criteria to define many water quality benchmarks. A large amount of effort goes into the process of assembling primary literature sources, extracting, transforming, and digitizing functions as well as carefully documenting various structured qualifiers/attributes. The ‘Online Database of Stressor-Response Functions for Native Trout and Salmon’ was created as a free and openly accessible database to facilitate sharing of stressor-response functions that now houses hundreds of digitized relationships with various download formats and supporting documentation. Interactive tools are also available within the database to overlay functions from different sources to evaluate variability, uncertainty, and tipping points. The database is set up as a content management system and a community of researchers continues to add and edit content (available at: https://mjbayly.com/stressor-response).</p>
          </div>
        `;
        document.body.appendChild(overlay);
  
        // Show it:
        overlay.style.display = 'flex';
  
        // Close logic:
        overlay.querySelector('.close-btn').addEventListener('click', () => {
          overlay.style.display = 'none';
        });
        // Also hide if they click outside the modal content:
        overlay.addEventListener('click', (e) => {
          if (e.target === overlay) {
            overlay.style.display = 'none';
          }
        });
  
        // Remember that we showed it:
        localStorage.setItem('stressorModalShown', '1');
      }
    };
  })(Drupal);
  