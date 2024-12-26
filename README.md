### Plugin Smart Assign para GLPI - Atribuição Automática de Técnicos por Categoria ou Grupo

O **plugin Smart Assign para GLPI** foi projetado para otimizar a atribuição de tickets, garantindo uma distribuição justa e inteligente da carga de trabalho entre técnicos. Com funcionalidades robustas e configuráveis, ele adapta-se às necessidades de diferentes equipes e organizações.

---

#### **Funcionalidades Principais**

1. **Atribuição Inteligente por Categoria ou Grupo**:
   - **Categoria**: Quando configurado para atribuição por categoria, o plugin distribui os tickets de forma igualitária dentro de cada categoria ITIL, com base nos membros do grupo encarregado.
   - **Grupo**: Quando configurado para atribuição por grupo, o plugin distribui os tickets entre todas as categorias que compartilham o mesmo grupo encarregado.

2. **Logs Automáticos e Gerenciáveis**:
   - Logs são gerados diariamente e excluídos automaticamente após 7 dias, garantindo um gerenciamento eficiente de espaço e informações.

3. **Adaptação Automática a Alterações no Grupo**:
   - O plugin ajusta-se automaticamente quando há mudanças nos grupos ou nos membros, sem necessidade de reconfiguração manual.

4. **Inclusão Opcional de Grupos como Atribuídos**:
   - Possibilidade de adicionar o grupo completo como atribuído aos tickets, útil para casos em que outros técnicos precisam acessar a fila (ex.: ausência de um técnico).

---

#### **Benefícios**
- Equilíbrio na distribuição de tarefas entre os técnicos.
- Flexibilidade para atender diferentes cenários de gerenciamento de tickets.
- Redução da carga administrativa com atribuição automatizada.
- Melhor organização e visibilidade das atribuições de tickets.

---

#### **Como Configurar**
1. **Instalação**:
   - Faça o download ou clone o repositório na pasta de plugins do GLPI.
   - Configure as permissões para a pasta do plugin:
     ```bash
     chown -R apache:apache /usr/share/glpi/plugins/smartassign
     ```
   - Ative o plugin no painel de administração do GLPI.

2. **Configuração no GLPI**:
   - Acesse a página de configurações do plugin e escolha entre "Categoria" ou "Grupo" como base para a atribuição.
   - Acesse a página de configurações do plugin e escolha entre atribuir grupo encarregado "Sim" ou "Não".

---

#### **Creditos**
Este plugin é um fork do RoundRobin (disponível em: [RoundRobin no GitHub](https://github.com/initiativa/roundrobin)), originalmente desenvolvido por [initiativa](https://github.com/initiativa).

---

**Observação**: Sou iniciante no GitHub e este é meu primeiro projeto. Estou aprendendo tanto sobre o desenvolvimento de plugins quanto sobre como interagir com a plataforma GitHub. Qualquer feedback ou sugestão será muito bem-vindo! 😊

**Aproveite essa solução prática e eficiente para melhorar a gestão de tickets no GLPI!**