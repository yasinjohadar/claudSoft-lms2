{{--
    Keeps the "البحث" select in step with the chosen category on the page form.

    Expects: $researchesJson (id/category_id/label/group) and $selectedResearchId.
    The elements are #doc_category_id and #doc_research_id.
--}}
<script>
(function () {
    var researches = @json($researchesJson);
    var selected = @json((string) ($selectedResearchId ?? ''));

    document.addEventListener('DOMContentLoaded', function () {
        var catSel = document.getElementById('doc_category_id');
        var resSel = document.getElementById('doc_research_id');
        if (!catSel || !resSel) return;

        function refresh(preselect) {
            var catId = catSel.value;
            var keep = preselect !== undefined ? preselect : resSel.value;

            resSel.innerHTML = '';
            resSel.appendChild(new Option('— بدون —', ''));

            researches
                .filter(function (r) { return String(r.category_id) === String(catId); })
                .forEach(function (r) { resSel.appendChild(new Option(r.label, r.id)); });

            // A research from the previous category simply drops out, which is
            // what the server-side "same category" rule would reject anyway.
            resSel.value = keep || '';
        }

        catSel.addEventListener('change', function () { refresh(''); });
        refresh(selected);
    });
})();
</script>
