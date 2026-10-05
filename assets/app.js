document.querySelectorAll('.fav').forEach(b => {
  b.onclick = async () => {
    b.classList.toggle('on'); // atualização otimista

    try {
      const r = await fetch('fav.php', {
        method: 'POST',
        body: new URLSearchParams({ id: b.dataset.id })
      });

      if (r.status == 401) {
        b.classList.toggle('on');
        location = 'auth.php?next=' + encodeURIComponent(
          location.pathname.split('/').pop() + location.search
        );
      } else if (!r.ok) {
        throw 0;
      }
    } catch {
      b.classList.toggle('on'); // rollback
      alert('Não foi possível atualizar o favorito. Tente de novo.');
    }
  };
});

document.querySelectorAll('form[data-once]').forEach(f => {
  f.onsubmit = () => {
    f.querySelector('button').disabled = true;
  };
});

document.querySelectorAll('.qty').forEach(i => {
  i.onchange = () => i.form.submit();
});
